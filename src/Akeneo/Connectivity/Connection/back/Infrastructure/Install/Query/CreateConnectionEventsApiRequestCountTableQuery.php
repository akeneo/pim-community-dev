<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install\Query;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateConnectionEventsApiRequestCountTableQuery
{
    const QUERY = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_events_api_request_count(
    minute INT NOT NULL,
    count INT NOT NULL,
    updated DATETIME NOT NULL,
    PRIMARY KEY (minute)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
