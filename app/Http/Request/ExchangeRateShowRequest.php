<?php declare(strict_types=1);

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ExchangeRateShowRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'date' => [Rule::date()],
        ];

        return $rules;
    }
}
