<?php declare(strict_types=1);

namespace App\Action;

use App\Action\Result\ConvertAmountResult;

interface ConvertAmountActionInterface
{
    public function execute(
        float $amount,
        string $currencyFrom,
        string $currencyTo,
        ?string $date,
    ): ConvertAmountResult;
}
