<?php declare(strict_types=1);

namespace App\Enum;

enum QueueTypeEnum: string
{
    case DEFAULT = 'default';
    case EXCHANGE_RATE = 'exchange-rate';

    /**
     * Get queue name depending on env
     */
    public function queueName(): string
    {
        return $this->value;
    }
}
