<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Install;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateConnectionAuditTableQuery
{
    public const QUERY = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_product(
    connection_code VARCHAR(100) NOT NULL,
    event_datetime DATETIME NOT NULL,
    event_count INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    updated DATETIME NOT NULL,
    PRIMARY KEY (event_datetime, connection_code, event_type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
