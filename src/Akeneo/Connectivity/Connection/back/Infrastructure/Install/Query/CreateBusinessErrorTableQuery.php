<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install\Query;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateBusinessErrorTableQuery
{
    const QUERY = <<<'SQL'
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_business_error(
    connection_code VARCHAR(100) NOT NULL,
    content JSON NOT NULL,
    error_datetime DATETIME NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
