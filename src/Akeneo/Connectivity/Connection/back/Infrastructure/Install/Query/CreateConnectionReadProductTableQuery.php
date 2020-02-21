<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install\Query;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateConnectionReadProductTableQuery
{
    const QUERY = <<<'SQL'
CREATE TABLE akeneo_connectivity_connection_audit_read_product(
    product_id INT NOT NULL,
    connection_code VARCHAR(100) NOT NULL,
    event_datetime DATETIME NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
