<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Action\FetchExchangeRatesAction;
use App\Http\Request\ExchangeRateShowRequest;
use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Job\FetchCbr;
use App\Module\ExchangeRate\Job\FetchNbrb;
use Illuminate\Http\JsonResponse;

final class ExchangeRateController extends Controller
{
    public function showAction(ExchangeRateShowRequest $request): JsonResponse
    {
        $data = $request->validated();
        $date = $data['date'] ?? null;

        $exchangeRates = ExchangeRate
            ::where('date', $date)
            ->get();

        return response()->json($exchangeRates);
    }

    public function parseAllAction(): JsonResponse
    {
        $action = new FetchExchangeRatesAction();

        $action->execute();

        return response()->json(['status' => 'ok']);
    }

    public function cbrAction(): JsonResponse
    {
        dispatch(new FetchCbr());

        return response()->json(['status' => 'ok']);
    }

    public function nbrbAction(): JsonResponse
    {
        dispatch(new FetchNbrb());

        return response()->json(['status' => 'ok']);
    }
}
