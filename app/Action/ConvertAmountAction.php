<?php declare(strict_types=1);

namespace App\Action;

use App\Action\Result\ConvertAmountResult;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Builder;

final class ConvertAmountAction implements ConvertAmountActionInterface
{
    public function execute(
        float|string $amount,
        string $currencyFrom,
        string $currencyTo,
        ?string $date,
    ): ConvertAmountResult
    {
        $amount = (float) $amount;

        // if we're converting from EUR to EUR, for instance, the amount will stay the same
        if ($currencyFrom === $currencyTo) {
            return ConvertAmountResult::make($amount, $amount);
        }

        $exchangeRate = $this->getMatchingExchangeRate($currencyFrom, $currencyTo, $date);

        return ConvertAmountResult::make($amount, $this->convert($amount, $exchangeRate, $currencyFrom), $exchangeRate);
    }

    private function convert(float $amount, ExchangeRate $exchangeRate, string $currencyFrom): float
    {
        if ($currencyFrom === $exchangeRate->{ExchangeRate::COLUMN_CURRENCY_FROM}->value) {
            return $amount * $exchangeRate->{ExchangeRate::COLUMN_RATE};
        }

        return $amount / $exchangeRate->{ExchangeRate::COLUMN_RATE};
    }

    private function getMatchingExchangeRate(
        string $currencyFrom,
        string $currencyTo,
        ?string $date,
    ): ExchangeRate
    {
        return ExchangeRate
            ::when($date !== null, static function ($qb) use ($date) {
                $qb->whereLike(ExchangeRate::COLUMN_DATE, $date . '%');
            })
            ->where(static function(Builder $query) use ($currencyFrom, $currencyTo): void {
                $query->orWhere(static function(Builder $query) use ($currencyFrom, $currencyTo): void {
                    $query->where(ExchangeRate::COLUMN_CURRENCY_FROM, $currencyFrom)
                        ->where(ExchangeRate::COLUMN_CURRENCY_TO, $currencyTo);
                });
                $query->orWhere(static function(Builder $query) use ($currencyFrom, $currencyTo): void {
                    $query->where(ExchangeRate::COLUMN_CURRENCY_TO, $currencyFrom)
                        ->where(ExchangeRate::COLUMN_CURRENCY_FROM, $currencyTo);
                });
            })
            ->orderBy(ExchangeRate::COLUMN_DATE, 'desc')
            ->firstOrFail();
    }
}
