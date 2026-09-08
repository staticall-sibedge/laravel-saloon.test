<?php declare(strict_types=1);

namespace App\Console\Command;

use App\Action\FetchExchangeRatesAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

#[AsCommand(name: 'exchange-rate:today')]
final class ExchangeRateToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rate:today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch today exchange rates';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dateAt = new CarbonImmutable();
        intro('Fetching exchanges date for: ' . $dateAt->toAtomString());

        $action = new FetchExchangeRatesAction();
        $action->execute();

        outro('Finished');
    }
}
