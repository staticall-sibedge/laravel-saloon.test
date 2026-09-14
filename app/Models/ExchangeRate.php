<?php declare(strict_types=1);

namespace App\Models;

use App\Http\Resource\ExchangeRateCollection;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use App\Module\ExchangeRate\Repository\ExchangeRateRepository;
use Database\Factories\ExchangeRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

#[UseResourceCollection(ExchangeRateCollection::class)]
#[Fillable([ExchangeRate::COLUMN_CURRENCY_FROM, ExchangeRate::COLUMN_CURRENCY_TO, ExchangeRate::COLUMN_RATE, ExchangeRate::COLUMN_DATE])]
class ExchangeRate extends ModelAbstract
{
    /** @use HasFactory<ExchangeRateFactory> */
    use HasFactory, Notifiable;
    public const string COLUMN_CURRENCY_FROM = 'currency_from';
    public const string COLUMN_CURRENCY_TO = 'currency_to';
    public const string COLUMN_RATE = 'rate';
    public const string COLUMN_DATE = 'date';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'currency_from' => CurrencyEnum::class,
            'currency_to' => CurrencyEnum::class,
            'rate' => 'float',
        ];
    }

    public static function getRepository(): ExchangeRateRepository
    {
        return new ExchangeRateRepository();
    }
}
