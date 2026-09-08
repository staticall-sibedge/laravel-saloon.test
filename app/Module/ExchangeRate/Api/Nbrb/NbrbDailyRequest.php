<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Api\Nbrb;

use App\Module\ExchangeRate\Dto\ExchangeRateEntry;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use App\Module\ExchangeRate\Exception\InvalidExchangeRateEntryException;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use ValueError;

final class NbrbDailyRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly DateTimeInterface|string|null $dateAt = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/exrates/rates';
    }

    public function createDtoFromResponse(Response $response): Collection
    {
        $currencyFrom = CurrencyEnum::BYN;
        /**
         * @var array{
         *     Cur_ID: int,
         *     Date: string,
         *     Cur_Abbreviation: string,
         *     Cur_Scale: int,
         *     Cur_Name: string,
         *     Cur_OfficialRate: float,
         * }[] $data
         */
        $data = $response->json();

        if (count($data) === 0) {
            throw new InvalidExchangeRateEntryException('Response data is empty');
        }

        $collection = new Collection();

        foreach ($data as $rateEntry) {
            // ensure that rate entry is valid, that we have currency to and exchange rate value
            // if we get an error here, log it as error (because maybe input is changed?) and skip this entry
            try {
                $this->validateRateEntry($rateEntry);
            } catch (InvalidExchangeRateEntryException $e) {
                Log::critical($e);

                throw $e;
            }

            $date = CarbonImmutable::parse($rateEntry['Date']);

            // if we can't map 'currencyTo' value, means we don't want it at this moment, so skip is safe
            // we can easily add it to 'CurrencyEnum' class, and it will be added on the next fetch
            try {
                $currencyTo = CurrencyEnum::from($rateEntry['Cur_Abbreviation'] === 'RUR' ? 'RUB' : $rateEntry['Cur_Abbreviation']);
            } catch (ValueError) {
                Log::notice('Unknown currency, ' . $rateEntry['Cur_Abbreviation']);

                continue;
            }

            $collection->add(
                new ExchangeRateEntry(
                    currencyFrom: $currencyFrom,
                    currencyTo: $currencyTo,
                    rate: $rateEntry['Cur_OfficialRate'],
                    date: $date,
                ),
            );
        }

        return $collection;
    }

    protected function defaultQuery(): array
    {
        $dateAt = CarbonImmutable::parse($this->dateAt);

        return [
            'periodicity' => '0',
            'ondate' => $dateAt->format('j/n/Y'),
        ];
    }

    /**
     * @param array{
     *     Cur_ID: int,
     *     Date: string,
     *     Cur_Abbreviation: string,
     *     Cur_Scale: int,
     *     Cur_Name: string,
     *     Cur_OfficialRate: float,
     * } $rateEntry
     *
     * @throws InvalidExchangeRateEntryException
     */
    private function validateRateEntry(array $rateEntry): void
    {
        if (array_key_exists('Cur_Abbreviation', $rateEntry) === false) {
            throw new InvalidExchangeRateEntryException('Unknown "currency to" value');
        }

        if (array_key_exists('Cur_OfficialRate', $rateEntry) === false) {
            throw new InvalidExchangeRateEntryException('Unknown "exchange rate" value');
        }

        if (is_numeric($rateEntry['Cur_OfficialRate']) === false || $rateEntry['Cur_OfficialRate'] <= 0) {
            throw new InvalidExchangeRateEntryException('"exchange rate" value is invalid, either non-numeric or <= 0');
        }
    }
}
