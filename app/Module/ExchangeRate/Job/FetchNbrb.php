<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Job;

use App\Job\JobAbstract;
use App\Module\ExchangeRate\Api\Nbrb\NbrbConnector;
use App\Module\ExchangeRate\Api\Nbrb\NbrbDailyRequest;
use Saloon\Http\Response;

final class FetchNbrb extends JobAbstract
{
    public function handle(): void
    {
        $connector = new NbrbConnector();
        $request = new NbrbDailyRequest();

        $response = $connector->send($request);

        foreach ($response->dtoOrFail() as $dto) {
            dispatch(new AddExchangeRateEntryJob($dto));
        }

        // @todo: Replace with `sendAsync`?
    }
}
