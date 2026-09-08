<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Service;

use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Dto\ExchangeRateEntry;
use Illuminate\Support\Facades\Cache;

final class ExchangeRateService
{
    public function addViaDto(ExchangeRateEntry $dto): ?ExchangeRate
    {
        $key = self::getCacheKey($dto->getCurrencyFrom()->value, $dto->getCurrencyTo()->value, $dto->getDate()->format('Y-m-d'));

        if (Cache::add($key, true, 3600) === false) {
            return ExchangeRate
                ::getRepository()
                ->findDuplicate($dto->getCurrencyFrom(), $dto->getCurrencyTo(), $dto->getDate());
        }

        $exchangeRate = new ExchangeRate();

        $exchangeRate->fill(
            [
                ExchangeRate::COLUMN_CURRENCY_FROM => $dto->getCurrencyFrom(),
                ExchangeRate::COLUMN_CURRENCY_TO => $dto->getCurrencyTo(),
                ExchangeRate::COLUMN_RATE => $dto->getRate(),
                ExchangeRate::COLUMN_DATE => $dto->getDate(),
            ],
        );

        $exchangeRate->save();

        // @todo: Add event when new exchange rate is added

        return $exchangeRate;
    }

    public static function getCacheKey(string $currencyFrom, string $currencyTo, string $date): string
    {
        return "exchange-rate:{$currencyFrom}_{$currencyTo}_{$date}";
    }
}
