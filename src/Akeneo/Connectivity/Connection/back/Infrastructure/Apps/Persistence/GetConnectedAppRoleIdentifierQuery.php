<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppRoleIdentifierQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectedAppRoleIdentifierQuery implements GetConnectedAppRoleIdentifierQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $appId): ?string
    {
        $query = <<<SQL
        SELECT role.role
        FROM akeneo_connectivity_connected_app app
        JOIN akeneo_connectivity_connection connection ON app.connection_code = connection.code
        JOIN oro_user_access_role user_role ON connection.user_id = user_role.user_id
        JOIN oro_access_role role ON role.id = user_role.role_id
        WHERE app.id = :app_id;
        SQL;

        $roleIdentifier = $this->connection->fetchOne(
            $query,
            [
                'app_id' => $appId,
            ]
        );

        return $roleIdentifier ?: null;
    }
}
