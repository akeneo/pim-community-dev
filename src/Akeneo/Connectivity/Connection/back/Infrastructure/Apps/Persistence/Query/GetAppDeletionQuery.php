<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppDeletion;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppDeletionQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppDeletionQuery implements GetAppDeletionQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $appId): AppDeletion
    {
        $query = <<<SQL
SELECT
    id,
    connection_code,
    user_group_name,
    (
        SELECT oro_access_role.role
        FROM oro_access_role
        JOIN oro_user_access_role ON oro_user_access_role.role_id = oro_access_role.id
        WHERE oro_user_access_role.user_id = akeneo_connectivity_connection.user_id
        LIMIT 1
    ) AS role
FROM akeneo_connectivity_connected_app
JOIN akeneo_connectivity_connection ON akeneo_connectivity_connection.code = akeneo_connectivity_connected_app.connection_code
WHERE id = :id
SQL;

        $row = $this->connection->fetchAssociative($query, [
            'id' => $appId,
        ]);

        return new AppDeletion(
            $row['id'],
            $row['connection_code'],
            $row['user_group_name'],
            $row['role'],
        );
    }
}
