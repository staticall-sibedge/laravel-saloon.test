<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

final class JobContextInit
{
    /**
     * Add extra context to queued jobs
     */
    public function handle(object $job, Closure $next)
    {
        Context::add([
            'request_id' => Str::uuid()->toString(),
            'env' => config('app.env'),
            'runtime.context' => config('app.runtime_context'),
            'env.worker' => config('app.env_worker'),
            'env.version' => config('app.version'),
        ]);

        return $next($job);
    }
}
