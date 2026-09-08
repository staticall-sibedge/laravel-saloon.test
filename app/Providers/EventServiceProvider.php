<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Events\EventServiceProvider as BaseEventServiceProvider;

class EventServiceProvider extends BaseEventServiceProvider
{
    public function shouldDiscoverEvents()
    {
        return true;
    }
}
