<?php declare(strict_types=1);

namespace App\Module\Queue\Job;

use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;

class RabbitMqBaseJob extends BaseJob
{
    /**
     * Fire the job.
     */
    public function fire(): void
    {
        $payload = $this->payload();

        // this is a standard Laravel job
        if (array_key_exists('job', $payload) === true) {
            parent::fire();

            return;
        }

        $class = CustomRabbitMqJob::class;
        $method = 'handle';

        ($this->instance = $this->resolve($class))->{$method}($this, $payload);

        $this->delete();
    }
}
