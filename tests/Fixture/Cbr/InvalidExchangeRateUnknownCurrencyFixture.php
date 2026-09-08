<?php declare(strict_types=1);

namespace Tests\Fixture\Cbr;

use Saloon\Http\Faking\Fixture;

final class InvalidExchangeRateUnknownCurrencyFixture extends Fixture
{
    protected function defineName(): string
    {
        return 'Cbr/InvalidExchangeRateUnknownCurrency';
    }
}
