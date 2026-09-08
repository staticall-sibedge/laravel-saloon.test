<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Dto;

use App\Module\ExchangeRate\Enum\CurrencyEnum;
use DateTimeInterface;
use JsonSerializable;

final class ExchangeRateEntry implements JsonSerializable
{
    public function __construct(
        private readonly CurrencyEnum $currencyFrom,
        private readonly CurrencyEnum $currencyTo,
        private readonly float $rate,
        private readonly DateTimeInterface $date,
    )
    {
    }

    public function getCurrencyFrom(): CurrencyEnum
    {
        return $this->currencyFrom;
    }

    public function getCurrencyTo(): CurrencyEnum
    {
        return $this->currencyTo;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function toArray(): array
    {
        return [
            'currencyFrom' => $this->currencyFrom,
            'currencyTo' => $this->currencyTo,
            'rate' => $this->rate,
            'date' => $this->date,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
