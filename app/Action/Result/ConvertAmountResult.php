<?php declare(strict_types=1);

namespace App\Action\Result;

use App\Action\ConvertAmountActionInterface;
use App\Models\ExchangeRate;

final class ConvertAmountResult
{
    private function __construct(public readonly float $amountToConvert, public readonly float $convertedAmount, public readonly ?ExchangeRate $exchangeRateUsed)
    {
    }

    public static function make(float $amountToConvert, float $convertedAmount, ?ExchangeRate $exchangeRateUsed = null): self
    {
        return new self($amountToConvert, $convertedAmount, $exchangeRateUsed);
    }
}
