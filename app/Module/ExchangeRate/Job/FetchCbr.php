<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Job;

use App\Job\JobAbstract;
use App\Module\ExchangeRate\Api\Cbr\CbrConnector;
use App\Module\ExchangeRate\Api\Cbr\CbrDailyRequest;

final class FetchCbr extends JobAbstract
{
    public function handle(): void
    {
        $connector = new CbrConnector();
        $request = new CbrDailyRequest();

        $response = $connector->send($request);

        foreach ($response->dtoOrFail() as $dto) {
            dispatch(new AddExchangeRateEntryJob($dto));
        }
    }
}
