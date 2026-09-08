<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Enum;

/**
 * @see https://en.wikipedia.org/wiki/ISO_4217
 */
enum CurrencyEnum: string
{
    case RUB = 'RUB';
    case BYN = 'BYN';
    case GBP = 'GBP';
    case PHP = 'PHP';
    case JPY = 'JPY';
    case CNY = 'CNY';
    case CAD = 'CAD';
    case EUR = 'EUR';
    case USD = 'USD';
}
