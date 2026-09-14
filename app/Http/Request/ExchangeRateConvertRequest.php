<?php declare(strict_types=1);

namespace App\Http\Request;

use App\Module\ExchangeRate\Enum\CurrencyEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ExchangeRateConvertRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', Rule::numeric()->between(0, 1_000_000_000_000)],
            'currencyFrom' => ['required', Rule::enum(CurrencyEnum::class)],
            'currencyTo' => ['required', Rule::enum(CurrencyEnum::class)],
            'date' => [Rule::date()],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'amount' => [
                'description' => 'How much money to convert',
                'example' => 1000.25,
            ],
            'currencyFrom' => [
                'description' => 'Amount currency',
                'example' => CurrencyEnum::RUB->value,
            ],
            'currencyTo' => [
                'description' => 'Result should be in this currency',
                'example' => CurrencyEnum::BYN->value,
            ],
            'date' => [
                'description' => 'See exchange rates for a specific date, in YYYY-MM-DD format',
                'example' => '2019-09-10',
            ],
        ];
    }
}
