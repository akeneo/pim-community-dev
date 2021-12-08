<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAllConnectedAppsPublicIdsInterface;
use Doctrine\DBAL\Connection;

class GetAllConnectedAppsPublicIdsQuery implements GetAllConnectedAppsPublicIdsInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        $query = <<<SQL
SELECT pim_api_client.marketplace_public_app_id
FROM akeneo_connectivity_connected_app
JOIN akeneo_connectivity_connection ON akeneo_connectivity_connected_app.connection_code = akeneo_connectivity_connection.code
JOIN pim_api_client on akeneo_connectivity_connection.client_id = pim_api_client.id
SQL;

        return $this->connection->fetchFirstColumn($query);
    }
}
