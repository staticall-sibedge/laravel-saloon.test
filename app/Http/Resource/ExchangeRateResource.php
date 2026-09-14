<?php declare(strict_types=1);

namespace App\Http\Resource;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ExchangeRate::COLUMN_ID => $this->{ExchangeRate::COLUMN_ID},
            ExchangeRate::COLUMN_CURRENCY_FROM => $this->{ExchangeRate::COLUMN_CURRENCY_FROM},
            ExchangeRate::COLUMN_CURRENCY_TO => $this->{ExchangeRate::COLUMN_CURRENCY_TO},
            ExchangeRate::COLUMN_DATE => $this->{ExchangeRate::COLUMN_DATE},
            ExchangeRate::COLUMN_RATE => $this->{ExchangeRate::COLUMN_RATE},
        ];
    }
}
