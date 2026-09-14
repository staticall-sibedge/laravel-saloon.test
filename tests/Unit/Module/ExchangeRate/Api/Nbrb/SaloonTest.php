<?php declare(strict_types=1);

namespace Tests\Unit\Module\ExchangeRate\Api\Nbrb;

use App\Models\ExchangeRate;
use App\Module\ExchangeRate\Api\Nbrb\NbrbDailyRequest as TestableRequest;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use App\Module\ExchangeRate\Exception\InvalidExchangeRateEntryException;
use App\Module\ExchangeRate\Job\FetchNbrb as TestableJob;
use App\Module\ExchangeRate\Service\ExchangeRateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockClient;
use Saloon\Laravel\Facades\Saloon;
use Tests\Fixture\Nbrb\EmptyResponseFixture;
use Tests\Fixture\Nbrb\InvalidExchangeRateNegativeFixture;
use Tests\Fixture\Nbrb\InvalidExchangeRateUnknownCurrencyFixture;
use Tests\Fixture\Nbrb\InvalidExchangeRateUnknownCurrencyWithCorrectFixture;
use Tests\Fixture\Nbrb\InvalidExchangeRateZeroFixture;
use Tests\Fixture\Nbrb\ValidExchangeRateMultipleFixture;
use Tests\Fixture\Nbrb\ValidExchangeRateSingleFixture;
use Tests\TestCase;

final class SaloonTest extends TestCase
{
    use LazilyRefreshDatabase;

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

    public function testShouldHandleInvalidExchangeRateUnknownCurrencyWithCorrect(): void
    {
        Saloon::fake([
            TestableRequest::class => new InvalidExchangeRateUnknownCurrencyWithCorrectFixture(),
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

        self::assertSame(CurrencyEnum::BYN, $rate->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(CurrencyEnum::CAD, $rate->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(2.2181, $rate->{ExchangeRate::COLUMN_RATE});
        self::assertSame('2026-09-08', $rate->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'));
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

        self::assertSame(CurrencyEnum::BYN, $rate->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(CurrencyEnum::CAD, $rate->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(2.2181, $rate->{ExchangeRate::COLUMN_RATE});
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
        $rateCad = ExchangeRate::where(ExchangeRate::COLUMN_CURRENCY_FROM, CurrencyEnum::CAD)->firstOrFail();

        self::assertSame(CurrencyEnum::BYN, $rateCad->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(CurrencyEnum::CAD, $rateCad->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(2.2181, $rateCad->{ExchangeRate::COLUMN_RATE});
        self::assertSame('2026-09-08', $rateCad->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'));

        /** @var ExchangeRate $rateByn */
        $rateByn = ExchangeRate::where(ExchangeRate::COLUMN_CURRENCY_FROM, CurrencyEnum::RUB)->firstOrFail();

        self::assertSame(CurrencyEnum::BYN, $rateByn->{ExchangeRate::COLUMN_CURRENCY_TO});
        self::assertSame(CurrencyEnum::RUB, $rateByn->{ExchangeRate::COLUMN_CURRENCY_FROM});
        self::assertSame(3.5714, $rateByn->{ExchangeRate::COLUMN_RATE});
        self::assertSame('2026-09-08', $rateByn->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'));
    }

    public function testShouldHandleSingleValidExchangeRateButItsAlreadyBeingAdded(): void
    {
        Saloon::fake([
            TestableRequest::class => new ValidExchangeRateSingleFixture(),
        ]);

        $exchangeRate = ExchangeRate::factory()->create(
            [
                ExchangeRate::COLUMN_CURRENCY_TO => CurrencyEnum::BYN,
                ExchangeRate::COLUMN_CURRENCY_FROM => CurrencyEnum::CAD,
                ExchangeRate::COLUMN_RATE => 12.24,
                ExchangeRate::COLUMN_DATE => CarbonImmutable::create(2026, 9, 8),
            ],
        );

        $exchangeRateTable = $exchangeRate->getTable();

        Cache::add(
            ExchangeRateService::getCacheKey(
                $exchangeRate->{ExchangeRate::COLUMN_CURRENCY_FROM}->value,
                $exchangeRate->{ExchangeRate::COLUMN_CURRENCY_TO}->value,
                $exchangeRate->{ExchangeRate::COLUMN_DATE}->format('Y-m-d'),
            ),
            true,
        );

        $this->assertDatabaseCount($exchangeRateTable, 1);

        $job = new TestableJob();

        $job->handle();

        $this->assertDatabaseCount($exchangeRateTable, 1);
    }
}
