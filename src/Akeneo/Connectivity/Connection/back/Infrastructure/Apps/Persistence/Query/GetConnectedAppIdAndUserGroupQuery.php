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

    public function execute(string $appPublicId): ?array
    {
        $selectQuery = <<<SQL
SELECT acca.id, oag.name
FROM pim_api_client pac
JOIN akeneo_connectivity_connection acc on pac.id = acc.client_id
JOIN akeneo_connectivity_connected_app acca on acc.code = acca.connection_code
JOIN oro_user_access_group ouag on acc.user_id = ouag.user_id
JOIN oro_access_group oag on ouag.group_id = oag.id
WHERE pac.marketplace_public_app_id = :id AND oag.name != 'All'
SQL;
        $resultRow = $this->connection->executeQuery($selectQuery, ['id' => $appPublicId])->fetch() ?: null;

        return null !== $resultRow ? [
            'appId' => $resultRow['id'],
            'userGroup' => $resultRow['name'],
        ] : null;
    }
}
