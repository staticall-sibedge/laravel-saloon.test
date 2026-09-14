<?php declare(strict_types=1);

namespace Api\ExchangeRate;

use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class ExchangeRateConvertTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $endpoint = '/api/exchange-rate/convert';

    public function testNoDataShouldThrowValidationError(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(422);
        $response->assertJsonCount(3, 'errors');

        $json = $response->decodeResponseJson()->json();

        self::assertArrayHasKey('amount', $json['errors']);
        self::assertArrayHasKey('currencyFrom', $json['errors']);
        self::assertArrayHasKey('currencyTo', $json['errors']);
    }

    public function testNegativeAmount(): void
    {
        $amount = -100;
        $currencyFrom = CurrencyEnum::RUB;
        $currencyTo = CurrencyEnum::RUB;

        $response = $this->get($this->endpoint . '?' . http_build_query(['amount' => $amount, 'currencyFrom' => $currencyFrom, 'currencyTo' => $currencyTo]));

        $response->assertStatus(422);
        $response->assertJsonCount(1, 'errors');

        $json = $response->decodeResponseJson()->json();

        self::assertArrayHasKey('amount', $json['errors']);
    }

    public function testNoModelSameCurrency(): void
    {
        $amount = 100;
        $currencyFrom = CurrencyEnum::RUB;
        $currencyTo = CurrencyEnum::RUB;

        $response = $this->get($this->endpoint . '?' . http_build_query(['amount' => $amount, 'currencyFrom' => $currencyFrom, 'currencyTo' => $currencyTo]));

        $response->assertStatus(200);

        $json = $response->decodeResponseJson()->json()['data'];

        self::assertSame($amount, $json['convertedAmount']);
        self::assertSame($amount, $json['amountToConvert']);
        self::assertSame(1, $json['rate']);
    }

    public function testNoModelDifferentCurrencyShouldErrorOut(): void
    {
        $amount = 100;
        $currencyFrom = CurrencyEnum::RUB;
        $currencyTo = CurrencyEnum::BYN;

        $response = $this->get($this->endpoint . '?' . http_build_query(['amount' => $amount, 'currencyFrom' => $currencyFrom, 'currencyTo' => $currencyTo]));

        $response->assertStatus(404); // no query results error
    }

    public function testDifferentCurrencyWithModelShouldConvert(): void
    {
        $amount = 100;
        $currencyFrom = CurrencyEnum::RUB;
        $currencyTo = CurrencyEnum::BYN;

        $exchangeRate = ExchangeRate::factory()->create(
            [
                ExchangeRate::COLUMN_CURRENCY_FROM => $currencyFrom,
                ExchangeRate::COLUMN_CURRENCY_TO => $currencyTo,
                ExchangeRate::COLUMN_RATE => 2.0,
            ],
        );

        $response = $this->get($this->endpoint . '?' . http_build_query(['amount' => $amount, 'currencyFrom' => $currencyFrom, 'currencyTo' => $currencyTo]));

        $response->assertStatus(200);

        $json = $response->decodeResponseJson()->json()['data'];

        self::assertSame($amount * $exchangeRate->{ExchangeRate::COLUMN_RATE}, (float) $json['convertedAmount']);
        self::assertSame($amount, $json['amountToConvert']);
        self::assertSame((float) $exchangeRate->{ExchangeRate::COLUMN_RATE}, (float) $json['rate']);
    }

    public function testDifferentCurrencyWithModelButInvertedShouldConvert(): void
    {
        $amount = 100;
        $currencyFrom = CurrencyEnum::RUB;
        $currencyTo = CurrencyEnum::BYN;

        $exchangeRate = ExchangeRate::factory()->create(
            [
                ExchangeRate::COLUMN_CURRENCY_FROM => $currencyTo,
                ExchangeRate::COLUMN_CURRENCY_TO => $currencyFrom,
                ExchangeRate::COLUMN_RATE => 2.0,
            ],
        );

        $response = $this->get($this->endpoint . '?' . http_build_query(['amount' => $amount, 'currencyFrom' => $currencyFrom, 'currencyTo' => $currencyTo]));

        $response->assertStatus(200);

        $json = $response->decodeResponseJson()->json()['data'];

        self::assertSame($amount / $exchangeRate->{ExchangeRate::COLUMN_RATE}, (float) $json['convertedAmount']);
        self::assertSame($amount, $json['amountToConvert']);
        self::assertSame((float) $exchangeRate->{ExchangeRate::COLUMN_RATE}, (float) $json['rate']);
    }
}
