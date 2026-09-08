<?php declare(strict_types=1);

namespace App\Module\ExchangeRate\Job;

use App\Job\JobAbstract;
use App\Module\ExchangeRate\Dto\ExchangeRateEntry;
use App\Module\ExchangeRate\Service\ExchangeRateService;

final class AddExchangeRateEntryJob extends JobAbstract
{
    public function __construct(
        private readonly ExchangeRateEntry $dto,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $service = app(ExchangeRateService::class);

        $service->addViaDto($this->dto);
    }
}
