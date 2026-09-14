<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Action\ConvertAmountAction;
use App\Action\FetchExchangeRatesAction;
use App\Http\Request\ExchangeRateConvertRequest;
use App\Http\Request\ExchangeRateShowRequest;
use App\Http\Resource\ExchangeRateCollection;
use App\Http\Resource\ExchangeRateConvertResource;
use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Job\FetchCbr;
use App\Module\ExchangeRate\Job\FetchNbrb;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Exchange rates', 'List or request fetching of exchange rates')]
final class ExchangeRateController extends Controller
{
    #[Endpoint(
        'List of exchange rates',
        'Returns a list of exchange rates, ordered by created date. You can specify a date (in YYYY-MM-DD format) and list exchange rates for that particular date only',
    )]
    #[Subgroup('Request')]
    #[ResponseFromApiResource(ExchangeRateCollection::class, ExchangeRate::class, cursorPaginate: 10)]
    public function showAction(ExchangeRateShowRequest $request): ExchangeRateCollection|ResourceCollection
    {
        $data = $request->validated();
        $date = $data['date'] ?? null;

        $exchangeRates = ExchangeRate
            ::when($date !== null, static function ($qb) use ($date) {
                $qb->whereLike(ExchangeRate::COLUMN_DATE, $date . '%');
            })
            ->orderBy(ExchangeRate::COLUMN_CREATED_AT, 'ASC')
            ->cursorPaginate(10);

        return $exchangeRates->toResourceCollection();
    }

    #[Endpoint(
        'Converts an amount from one currency to another',
        'You can specify a date (in YYYY-MM-DD format) and conversion will be using that particular date as a reference (otherwise, current date will be used)',
    )]
    #[Subgroup('Request')]
    #[ResponseFromApiResource(ExchangeRateConvertResource::class)]
    public function convertAction(ExchangeRateConvertRequest $request): ExchangeRateConvertResource
    {
        $data = $request->validated();
        $currencyFrom = $data['currencyFrom'];
        $currencyTo = $data['currencyTo'];
        $date = $data['date'] ?? null;

        $action = new ConvertAmountAction();

        $convertedAmount = $action->execute($data['amount'], $currencyFrom, $currencyTo, $date);

        return new ExchangeRateConvertResource($convertedAmount);
    }

    #[Endpoint(
        'Exchange rates for every configured provider'
    )]
    #[Subgroup('Fetch')]
    #[Response(<<<JSON
{
  "status": "ok"
}
JSON)]
    public function parseAllAction(): JsonResponse
    {
        $action = new FetchExchangeRatesAction();

        $action->execute();

        return response()->json(['status' => 'ok']);
    }

    #[Endpoint(
        'Exchange rates for Central Bank of Russia (CBR)',
        'Fetches exchange rates for a provider. It will take some time to fetch them',
    )]
    #[Subgroup('Fetch')]
    #[Response(<<<JSON
{
  "status": "ok"
}
JSON)]
    public function cbrAction(): JsonResponse
    {
        dispatch(new FetchCbr());

        return response()->json(['status' => 'ok']);
    }

    #[Endpoint(
        'Exchange rates for National Bank of the Republic of Belarus (NBRN)',
        'Fetches exchange rates for a provider. It will take some time to fetch them',
    )]
    #[Subgroup('Fetch')]
    #[Response(<<<JSON
{
  "status": "ok"
}
JSON)]
    public function nbrbAction(): JsonResponse
    {
        dispatch(new FetchNbrb());

        return response()->json(['status' => 'ok']);
    }
}
