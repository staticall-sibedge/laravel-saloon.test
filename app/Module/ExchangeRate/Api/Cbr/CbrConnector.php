<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Api\Cbr;

use Saloon\Http\Connector;

final class CbrConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://www.cbr-xml-daily.ru/';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
