<?php declare(strict_types=1);

namespace Tests\Fixture\Cbr;

use Saloon\Http\Faking\Fixture;

final class InvalidExchangeRateZeroFixture extends Fixture
{
    protected function defineName(): string
    {
        return 'Cbr/InvalidExchangeRateZero';
    }
}
