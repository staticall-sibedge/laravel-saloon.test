<?php declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\JobContextInit;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        self::setProhibitedCommands();
        Validator::excludeUnvalidatedArrayKeys();

        // global job middleware
        Bus::pipeThrough([
            JobContextInit::class,
        ]);
    }

    /**
     * Prohibited commands
     */
    protected static function setProhibitedCommands(): void
    {
        $app = app();
        $testingOnly = $app->runningUnitTests() === false;
        $localOnly = !$app->isLocal();

        // allow only on test
        DB::prohibitDestructiveCommands($testingOnly);

        // only local and test
        RollbackCommand::prohibit($localOnly && $testingOnly);
    }
}
