<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Install;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateConnectionAuditErrorTableQuery
{
    public const QUERY = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_error(
    connection_code VARCHAR(100) NOT NULL,
    error_datetime DATETIME NOT NULL,
    error_count INT NOT NULL,
    error_type VARCHAR(100) NOT NULL,
    PRIMARY KEY (error_datetime, connection_code, error_type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
