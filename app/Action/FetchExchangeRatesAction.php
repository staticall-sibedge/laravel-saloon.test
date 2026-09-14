<?php declare(strict_types=1);

namespace App\Action;

use App\Module\ExchangeRate\Job\FetchCbr;
use App\Module\ExchangeRate\Job\FetchNbrb;

final class FetchExchangeRatesAction implements FetchExchangeRatesActionInterface
{
    public function execute(): void
    {
        dispatch(new FetchCbr());
        dispatch(new FetchNbrb());
    }
}
