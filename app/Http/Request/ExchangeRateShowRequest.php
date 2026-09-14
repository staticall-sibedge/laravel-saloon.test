<?php declare(strict_types=1);

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ExchangeRateShowRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => [Rule::date()],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'date' => [
                'description' => 'See exchange rates for a specific date, in YYYY-MM-DD format',
                'example' => '2019-09-10',
            ],
        ];
    }
}
