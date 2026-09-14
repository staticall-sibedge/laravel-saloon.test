<?php declare(strict_types=1);

namespace App\Http\Resource;

use App\Action\Result\ConvertAmountResult;
use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ExchangeRateConvertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ConvertAmountResult $resource */
        $resource = $this->resource;

        return [
            'data' => [
                'amountToConvert' => $resource->amountToConvert,
                'convertedAmount' => $resource->convertedAmount,
                'date' => $resource->exchangeRateUsed?->{ExchangeRate::COLUMN_DATE} ?? new CarbonImmutable(),
                'rate' => (float) ($resource->exchangeRateUsed?->{ExchangeRate::COLUMN_RATE} ?? 1),
            ],
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
