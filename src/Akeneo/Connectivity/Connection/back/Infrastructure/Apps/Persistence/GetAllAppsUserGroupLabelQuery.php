<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Doctrine\DBAL\Connection;

class GetAllAppsUserGroupLabelQuery
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @return array<array<'code': string, 'label': string>>|array<>
     */
    public function execute(): array
    {
        $sql = <<<SQL
            SELECT access_group.name as code, connection.label as label
            FROM akeneo_connectivity_connection connection
            JOIN oro_user user ON user.id = connection.user_id
            JOIN oro_user_access_group user_access_group ON user.id = user_access_group.user_id
            JOIN oro_access_group access_group ON user_access_group.group_id = access_group.id
            WHERE connection.type = 'app'
        SQL;

        return $this->connection->fetchAllAssociative($sql);
    }
}
