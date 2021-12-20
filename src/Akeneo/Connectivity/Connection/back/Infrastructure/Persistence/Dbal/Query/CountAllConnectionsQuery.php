<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\CountAllConnectionsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountAllConnectionsQuery implements CountAllConnectionsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): int
    {
        $sql = <<<SQL
SELECT COUNT(*) as count
FROM akeneo_connectivity_connection;
SQL;

        return (int) $this->connection->executeQuery($sql)->fetchOne();
    }
}
