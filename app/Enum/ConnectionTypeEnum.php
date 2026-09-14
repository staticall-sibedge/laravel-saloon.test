<?php declare(strict_types=1);

namespace App\Enum;

enum ConnectionTypeEnum: string
{
    case DEFAULT = 'mysql';
    case CLICKHOUSE = 'clickhouse';
}
