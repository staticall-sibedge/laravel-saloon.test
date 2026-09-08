<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Api\Nbrb;

use Saloon\Http\Connector;

final class NbrbConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://api.nbrb.by/';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
