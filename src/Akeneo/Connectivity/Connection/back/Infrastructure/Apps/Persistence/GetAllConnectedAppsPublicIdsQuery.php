<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllConnectedAppsPublicIdsInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllConnectedAppsPublicIdsQuery implements GetAllConnectedAppsPublicIdsInterface
{
    public function __construct(private Connection $connection)
    {
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
