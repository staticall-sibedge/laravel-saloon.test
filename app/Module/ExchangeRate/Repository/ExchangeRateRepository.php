<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Repository;

use App\Models\ExchangeRate as Model;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use App\Repository\RepositoryInterface;
use DateTimeInterface;

final class ExchangeRateRepository implements RepositoryInterface
{
    public function findDuplicate(CurrencyEnum $currencyFrom, CurrencyEnum $currencyTo, DateTimeInterface $date): ?Model
    {
        return Model
            ::where(Model::COLUMN_CURRENCY_FROM, $currencyFrom->value)
            ->where(Model::COLUMN_CURRENCY_TO, $currencyTo->value)
            ->whereLike(Model::COLUMN_DATE, $date->format('Y-m-d') . '%')
            ->first();
    }
}
