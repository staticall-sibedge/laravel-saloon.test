<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Api\Cbr;

use App\Module\ExchangeRate\Dto\ExchangeRateEntry;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use App\Module\ExchangeRate\Exception\InvalidExchangeRateEntryException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use ValueError;

final class CbrDailyRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/daily_json.js';
    }

    public function createDtoFromResponse(Response $response): Collection
    {
        $currencyFrom = CurrencyEnum::RUB;
        /**
         * @var array{
         *     Date: string,
         *     PreviousDate: string,
         *     PreviousURL: string,
         *     Timestamp: string,
         *     Valute: array<string, array{
         *         ID: string,
         *         NumCode: string,
         *         CharCode: string,
         *         Nominal: int,
         *         Name: string,
         *         Value: float,
         *         Previous: float,
         *     }>,
         * } $data
         */
        $data = $response->json();
        $collection = new Collection();

        if (array_key_exists('Date', $data) === false) {
            throw new InvalidExchangeRateEntryException('No date key exists in response data');
        }

        $date = CarbonImmutable::parse($data['Date']);

        foreach ($data['Valute'] as $rateEntry) {
            // ensure that rate entry is valid, that we have currency to and exchange rate value
            // if we get an error here, log it as error (because maybe input is changed?) and rethrow it
            try {
                $this->validateRateEntry($rateEntry);
            } catch (InvalidExchangeRateEntryException $e) {
                Log::critical($e);

                throw $e;
            }

            // if we can't map 'currencyTo' value, means we don't want it at this moment, so skip is safe
            // we can easily add it to 'CurrencyEnum' class, and it will be added on the next fetch
            try {
                $currencyTo = CurrencyEnum::from($rateEntry['CharCode']);
            } catch (ValueError) {
                Log::notice('Unknown currency, ' . $rateEntry['CharCode']);

                continue;
            }

            $collection->add(
                new ExchangeRateEntry(
                    currencyFrom: $currencyFrom,
                    currencyTo: $currencyTo,
                    rate: $rateEntry['Value'],
                    date: $date,
                ),
            );
        }

        return $collection;
    }

    /**
     * @param array{
     *     ID: string,
     *     NumCode: string,
     *     CharCode: string,
     *     Nominal: int,
     *     Name: string,
     *     Value: float,
     *     Previous: float,
     * } $rateEntry
     * @throws InvalidExchangeRateEntryException
     */
    private function validateRateEntry(array $rateEntry): void
    {
        if (array_key_exists('CharCode', $rateEntry) === false || $rateEntry['CharCode'] === '') {
            throw new InvalidExchangeRateEntryException('Invalid "currency to" value');
        }

        if (array_key_exists('Value', $rateEntry) === false) {
            throw new InvalidExchangeRateEntryException('Unknown "exchange rate" value');
        }

        if (is_numeric($rateEntry['Value']) === false || $rateEntry['Value'] <= 0) {
            throw new InvalidExchangeRateEntryException('"exchange rate" value is invalid, either non-numeric or <= 0');
        }
    }
}
