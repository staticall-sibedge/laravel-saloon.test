<?php declare(strict_types=1);

namespace Tests\Unit\Module\ExchangeRate\Api\Cbr;

use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Api\Cbr\CbrDailyRequest as TestableRequest;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use App\Module\ExchangeRate\Exception\InvalidExchangeRateEntryException;
use App\Module\ExchangeRate\Job\FetchCbr as TestableJob;
use App\Module\ExchangeRate\Service\ExchangeRateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockClient;
use Saloon\Laravel\Facades\Saloon;
use Tests\Fixture\Cbr\EmptyResponseFixture;
use Tests\Fixture\Cbr\InvalidExchangeRateNegativeFixture;
use Tests\Fixture\Cbr\InvalidExchangeRateUnknownCurrencyFixture;
use Tests\Fixture\Cbr\InvalidExchangeRateZeroFixture;
use Tests\Fixture\Cbr\ValidExchangeRateMultipleFixture;
use Tests\Fixture\Cbr\ValidExchangeRateSingleFixture;
use Tests\TestCase;

final class SaloonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
        MockClient::destroyGlobal();
    }

    public function testShouldHandleEmptyResponse(): void
    {
        Saloon::fake([
            TestableRequest::class => new EmptyResponseFixture(),
        ]);

        $exchangeRate = new ExchangeRate();
        $exchangeRateTable = $exchangeRate->getTable();
        unset($exchangeRate);

        $this->assertDatabaseCount($exchangeRateTable, 0);

        $job = new TestableJob();

        $this->expectException(InvalidExchangeRateEntryException::class);
        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 0);
    }

    public function testShouldHandleInvalidExchangeRateZero(): void
    {
        Saloon::fake([
            TestableRequest::class => new InvalidExchangeRateZeroFixture(),
        ]);

        $exchangeRate = new ExchangeRate();
        $exchangeRateTable = $exchangeRate->getTable();
        unset($exchangeRate);

        $this->assertDatabaseCount($exchangeRateTable, 0);

        $job = new TestableJob();

        $this->expectException(InvalidExchangeRateEntryException::class);
        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 0);
    }

    public function testShouldHandleInvalidExchangeRateNegative(): void
    {
        Saloon::fake([
            TestableRequest::class => new InvalidExchangeRateNegativeFixture(),
        ]);

        $exchangeRate = new ExchangeRate();
        $exchangeRateTable = $exchangeRate->getTable();
        unset($exchangeRate);

        $this->assertDatabaseCount($exchangeRateTable, 0);

        $job = new TestableJob();

        $this->expectException(InvalidExchangeRateEntryException::class);
        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 0);
    }

    public function testShouldHandleInvalidExchangeRateUnknownCurrency(): void
    {
        Saloon::fake([
            TestableRequest::class => new InvalidExchangeRateUnknownCurrencyFixture(),
        ]);

        $exchangeRate = new ExchangeRate();
        $exchangeRateTable = $exchangeRate->getTable();
        unset($exchangeRate);

        $this->assertDatabaseCount($exchangeRateTable, 0);

        $job = new TestableJob();

        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 0);
    }

    public function testShouldHandleSingleValidExchangeRate(): void
    {
        Saloon::fake([
            TestableRequest::class => new ValidExchangeRateSingleFixture(),
        ]);

        $exchangeRate = new ExchangeRate();
        $exchangeRateTable = $exchangeRate->getTable();
        unset($exchangeRate);

        $this->assertDatabaseCount($exchangeRateTable, 0);

        $job = new TestableJob();

        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 1);

        /** @var ExchangeRate $rate */
        $rate = ExchangeRate::firstOrFail();

        self::assertSame(CurrencyEnum::RUB, $rate->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(CurrencyEnum::CAD, $rate->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(78.1241, $rate->{ExchangeRate::COLUMN_RATE});
        self::assertSame('2026-09-08', $rate->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'));
    }

    public function testShouldHandleMultipleValidExchangeRate(): void
    {
        Saloon::fake([
            TestableRequest::class => new ValidExchangeRateMultipleFixture(),
        ]);

        $exchangeRate = new ExchangeRate();
        $exchangeRateTable = $exchangeRate->getTable();
        unset($exchangeRate);

        $this->assertDatabaseCount($exchangeRateTable, 0);

        $job = new TestableJob();

        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 2);

        /** @var ExchangeRate $rateCad */
        $rateCad = ExchangeRate::where('currency_to', CurrencyEnum::CAD)->firstOrFail();

        self::assertSame(CurrencyEnum::RUB, $rateCad->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(CurrencyEnum::CAD, $rateCad->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(78.1241, $rateCad->{ExchangeRate::COLUMN_RATE});
        self::assertSame('2026-09-08', $rateCad->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'));

        /** @var ExchangeRate $rateByn */
        $rateByn = ExchangeRate::where('currency_to', CurrencyEnum::BYN)->firstOrFail();

        self::assertSame(CurrencyEnum::RUB, $rateByn->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(CurrencyEnum::BYN, $rateByn->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(28.9898, $rateByn->{ExchangeRate::COLUMN_RATE});
        self::assertSame('2026-09-08', $rateByn->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'));
    }

    public function testShouldHandleSingleValidExchangeRateButItsAlreadyBeingAdded(): void
    {
        Saloon::fake([
            TestableRequest::class => new ValidExchangeRateSingleFixture(),
        ]);

        $exchangeRate = ExchangeRate::factory()->create(
            [
                ExchangeRate::COLUMN_CURRENCY_FROM => CurrencyEnum::RUB,
                ExchangeRate::COLUMN_CURRENCY_TO => CurrencyEnum::CAD,
                ExchangeRate::COLUMN_RATE => 12.24,
                ExchangeRate::COLUMN_DATE => CarbonImmutable::create(2026, 9, 8),
            ],
        );

        $exchangeRateTable = $exchangeRate->getTable();

        Cache::add(ExchangeRateService::getCacheKey($exchangeRate->{ExchangeRate::COLUMN_CURRENCY_FROM}->value, $exchangeRate->{ExchangeRate::COLUMN_CURRENCY_TO}->value, $exchangeRate->{ExchangeRate::COLUMN_DATE}->format('Y-m-d')), true);

        $this->assertDatabaseCount($exchangeRateTable, 1);

        $job = new TestableJob();

        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 1);
    }
}
