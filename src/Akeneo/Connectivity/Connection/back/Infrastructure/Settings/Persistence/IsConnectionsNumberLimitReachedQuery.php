<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Service\GetConnectionsNumberLimit;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedQuery implements IsConnectionsNumberLimitReachedQueryInterface
{
    public function __construct(
        private Connection $connection,
        private GetConnectionsNumberLimit $getConnectionsNumberLimit
    ) {
    }

    public function execute(): bool
    {
        $sql = <<<SQL
SELECT COUNT(*) as count
FROM akeneo_connectivity_connection;
SQL;

        $connectionCount = (int) $this->connection->executeQuery($sql)->fetchOne();

        return $connectionCount >= $this->getConnectionsNumberLimit->getLimit();
    }
}
