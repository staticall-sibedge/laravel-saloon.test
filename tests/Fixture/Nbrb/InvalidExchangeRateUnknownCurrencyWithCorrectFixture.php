<?php declare(strict_types=1);

namespace Tests\Fixture\Nbrb;

use Saloon\Http\Faking\Fixture;

final class InvalidExchangeRateUnknownCurrencyWithCorrectFixture extends Fixture
{
    protected function defineName(): string
    {
        return 'Nbrb/InvalidExchangeRateUnknownCurrencyWithCorrect';
    }
}
