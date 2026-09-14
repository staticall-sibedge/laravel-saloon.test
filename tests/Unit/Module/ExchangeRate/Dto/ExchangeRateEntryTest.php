<?php declare(strict_types=1);

namespace Tests\Unit\Module\ExchangeRate\Dto;

use App\Module\ExchangeRate\Dto\ExchangeRateEntry;
use App\Module\ExchangeRate\Enum\CurrencyEnum;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class ExchangeRateEntryTest extends TestCase
{
    public function test(): void
    {
        $date = CarbonImmutable::today();
        $dto = new ExchangeRateEntry(CurrencyEnum::ALL, CurrencyEnum::PHP, 5.5, $date);

        self::assertSame(CurrencyEnum::ALL, $dto->getCurrencyFrom());
        self::assertSame(CurrencyEnum::PHP, $dto->getCurrencyTo());
        self::assertSame(5.5, $dto->getRate());
        self::assertSame($date, $dto->getDate());

        self::assertSame($dto->jsonSerialize(), $dto->toArray());
    }
}
