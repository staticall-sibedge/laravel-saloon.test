<?php declare(strict_types=1);

namespace Tests\Feature\Api\ExchangeRate;

use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class ExchangeRateListTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $endpoint = '/api/exchange-rate';

    public function testNoData(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function testShouldApplyFilter(): void
    {
        $date = '2025-12-12';

        $currenciesFrom = array_map(static fn(CurrencyEnum $currencyEnum) => $currencyEnum->value, CurrencyEnum::cases());
        $currenciesTo = $currenciesFrom;
        $ids = [];

        foreach ($currenciesFrom as $currencyFrom) {
            $currencyTo = array_pop($currenciesTo);

            ExchangeRate::factory()->create(
                [
                    ExchangeRate::COLUMN_CURRENCY_FROM => $currencyFrom,
                    ExchangeRate::COLUMN_CURRENCY_TO => $currencyTo,
                    ExchangeRate::COLUMN_DATE => CarbonImmutable::createFromFormat('Y-m-d', '2020-01-01'),
                ],
            );
            $ids[] = ExchangeRate::factory()->create(
                [
                    ExchangeRate::COLUMN_CURRENCY_FROM => $currencyFrom,
                    ExchangeRate::COLUMN_CURRENCY_TO => $currencyTo,
                    ExchangeRate::COLUMN_DATE => CarbonImmutable::createFromFormat('Y-m-d', $date),
                ],
            )->id;
        }

        $response = $this->get($this->endpoint . '?date=' . $date);

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');

        $data = $response->decodeResponseJson()->json();

        foreach ($data['data'] as $item) {
            self::assertSame(array_shift($ids), $item['id']);
        }
    }

    public function testShouldApplyFilterButNoResourcesFound(): void
    {
        $date = '2025-12-12';

        ExchangeRate::factory()->create(
            [
                ExchangeRate::COLUMN_CURRENCY_FROM => CurrencyEnum::EUR,
                ExchangeRate::COLUMN_CURRENCY_TO => CurrencyEnum::RUB,
                ExchangeRate::COLUMN_DATE => CarbonImmutable::createFromFormat('Y-m-d', '2020-01-01'), // date mismatch on purpose
            ],
        );

        $response = $this->get($this->endpoint . '?date=' . $date);

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }
}
