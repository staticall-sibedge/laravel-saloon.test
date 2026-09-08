<?php declare(strict_types=1);

namespace App\Job;

use App\Enum\QueueTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

abstract class JobAbstract implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct()
    {
        $this->onQueue(QueueTypeEnum::DEFAULT);
    }

    public function onQueue(string|QueueTypeEnum $queue): self
    {
        if (is_string($queue)) {
            $queue = QueueTypeEnum::from($queue);
        }

        $this->queue = $queue->queueName();

        return $this;
    }
}
