<?php

namespace Akeneo\UserManagement\Infrastructure\Storage;

use Akeneo\UserManagement\Application\Storage\FindUserGroups;
use Akeneo\UserManagement\Domain\Model\Group;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class SqlFindUserGroups implements FindUserGroups
{
    public function __construct(
        private Connection $connection,
    ) {

    }

    /**
     * {@inheritDoc}
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        ?int $limit = self::DEFAULT_LIMIT,
    ): array
    {
        $groupIds = $this->filter($search, $searchAfterId, $limit);

        $query = <<<SQL
WITH group_roles as (
    SELECT ouagr.group_id, JSON_ARRAYAGG(
        JSON_OBJECT(
            "id", oar.id,
            "role", oar.role,
            "label", oar.label,
            "type", oar.type
        )
    ) AS roles
    FROM oro_access_role oar
    JOIN oro_user_access_group_role ouagr ON oar.id = ouagr.role_id
    WHERE ouagr.group_id IN (:group_ids)
    GROUP BY ouagr.group_id
)
SELECT 
    oag.id,
    oag.name, 
    oag.type, 
    oag.default_permissions,
    COALESCE(gr.roles, '[]') as roles
FROM oro_access_group oag
LEFT JOIN group_roles gr ON  gr.group_id = oag.id
WHERE oag.type = 'default'
AND oag.id IN (:group_ids)
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['group_ids' => $groupIds],
            ['group_ids' => Connection::PARAM_INT_ARRAY]
        )->fetchAllAssociative();
        
        return array_map(function ($data) {
            return Group::createFromDatabase($data);
        }, $results);
    }

    private function filter(
        ?string $search,
        ?int $searchAfterId,
        ?int $limit,
    ): array
    {
        $searchSql = '';
        if ($search !== null) {
            $searchSql = <<<SQL
    AND oag.name LIKE '%${search}%'
SQL;
        }

        $searchAfterIdSql = '';
        if ($searchAfterId !== null) {
            $searchAfterIdSql = <<<SQL
    AND oag.id > ${searchAfterId}
SQL;
        }
        $limitSql = '';
        if ($limit !== null) {
            $limitSql = <<<SQL
    LIMIT ${limit}
SQL;
        }

        $query = <<<SQL
SELECT id
FROM oro_access_group oag
WHERE oag.type = 'default'
${searchSql}
${searchAfterIdSql}
ORDER BY oag.id
${limitSql} 
SQL;

        return $this->connection->executeQuery(
            $query
        )->fetchFirstColumn();
    }
}
