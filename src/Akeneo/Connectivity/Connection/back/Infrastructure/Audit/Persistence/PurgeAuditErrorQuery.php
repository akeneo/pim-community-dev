<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeAuditErrorQuery
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(\DateTimeImmutable $before): int
    {
        $deleteQuery = <<<SQL
DELETE FROM akeneo_connectivity_connection_audit_error
WHERE error_datetime < :before
SQL;
        return $this->connection->executeStatement(
            $deleteQuery,
            ['before' => $before],
            ['before' => Types::DATETIME_IMMUTABLE]
        );
    }
}
