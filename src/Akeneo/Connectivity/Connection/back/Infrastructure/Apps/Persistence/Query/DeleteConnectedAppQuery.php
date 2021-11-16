<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\DeleteConnectedAppQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteConnectedAppQuery implements DeleteConnectedAppQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $appId): void
    {
        $query = <<<SQL
DELETE FROM akeneo_connectivity_connected_app
WHERE id = :id
SQL;

        $this->connection->executeQuery($query, [
            'id' => $appId,
        ]);
    }
}
