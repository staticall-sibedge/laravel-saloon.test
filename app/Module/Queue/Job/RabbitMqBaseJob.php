<?php declare(strict_types=1);

namespace App\Module\Queue\Job;

use Illuminate\Queue\Jobs\JobName;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;

class RabbitMqBaseJob extends BaseJob
{

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire(): void
    {
        $payload = $this->payload();

        // this is a standard Laravel job
        if (array_key_exists('job', $payload) === true) {
            parent::fire();

            return;
        }

        $class = WhatheverClassNameToExecute::class;
        $method = 'handle';

        ($this->instance = $this->resolve($class))->{$method}($this, $payload);

        $this->delete();
    }
}
