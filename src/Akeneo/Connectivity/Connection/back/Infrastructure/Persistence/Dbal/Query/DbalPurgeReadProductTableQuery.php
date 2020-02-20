<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeReadProductTableQuery;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalPurgeReadProductTableQuery implements PurgeReadProductTableQuery
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(\DateTime $before): int
    {
        $deleteQuery = <<<SQL
DELETE FROM akeneo_connectivity_connection_read_product
WHERE event_datetime < :before
SQL;
        return $this->connection->executeUpdate(
            $deleteQuery,
            ['before' => $before->format('Y-m-d h:i:s')]
        );
    }
}
