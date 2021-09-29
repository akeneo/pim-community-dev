<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetConnectedAppIdAndUserGroupQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppIdAndUserGroupQuery implements GetConnectedAppIdAndUserGroupQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $marketplaceAppId): ?array
    {
        $query = <<<SQL
SELECT akeneo_connectivity_connected_app.id as appId, oro_access_group.name as userGroup
FROM pim_api_client
JOIN akeneo_connectivity_connection on pim_api_client.id = akeneo_connectivity_connection.client_id
JOIN akeneo_connectivity_connected_app on akeneo_connectivity_connection.code = akeneo_connectivity_connected_app.connection_code
JOIN oro_user_access_group on akeneo_connectivity_connection.user_id = oro_user_access_group.user_id
JOIN oro_access_group on oro_user_access_group.group_id = oro_access_group.id
WHERE pim_api_client.marketplace_public_app_id = :marketplace_public_app_id
AND oro_access_group.name != 'All'
SQL;

        $stmt = $this->connection->executeQuery($query, [
            'marketplace_public_app_id' => $marketplaceAppId,
        ]);
        $row = $stmt->fetch();

        return false === $row ? null : $row;
    }
}
