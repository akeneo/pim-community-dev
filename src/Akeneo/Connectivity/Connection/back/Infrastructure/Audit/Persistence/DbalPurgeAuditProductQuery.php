<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\PurgeAuditProductQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalPurgeAuditProductQuery implements PurgeAuditProductQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(\DateTimeImmutable $before): int
    {
        $deleteQuery = <<<SQL
DELETE FROM akeneo_connectivity_connection_audit_product
WHERE event_datetime < :before
SQL;
        return $this->connection->executeUpdate(
            $deleteQuery,
            ['before' => $before],
            ['before' => Types::DATETIME_IMMUTABLE]
        );
    }
}
