<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Api\Nbrb;

use App\Module\ExchangeRate\Enum\CurrencyEnum;

final class NbrbCurrencyMapper
{
    public const string RUSSIAN_ROUBLE_IN_NBRB = 'RUR';

    public static function convert(string $currencyFromNbrb): string
    {
        return $currencyFromNbrb === self::RUSSIAN_ROUBLE_IN_NBRB ? CurrencyEnum::RUB->value : $currencyFromNbrb;
    }
}
