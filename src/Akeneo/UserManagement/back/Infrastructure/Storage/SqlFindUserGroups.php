<?php

namespace Akeneo\UserManagement\back\Infrastructure\Storage;

use Akeneo\UserManagement\back\Application\Storage\FindUserGroups;
use Akeneo\UserManagement\back\Domain\Model\Group;
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
        $query = $this->buildQuery($search, $searchAfterId, $limit);

        $results = $this->connection->executeQuery(
            $query
        )->fetchAllAssociative();

        return array_map(function ($data) {
            return Group::createFromDatabase($data);
        }, $results);
    }

    private function buildQuery(
        ?string $search,
        ?int $searchAfterId,
        ?int $limit,
    ): string
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
SELECT 
    oag.id,
    oag.name, 
    oag.type, 
    oag.default_permissions
FROM oro_access_group oag
WHERE oag.type = 'default'
${searchSql}
${searchAfterIdSql}
ORDER BY oag.id
${limitSql} 
SQL;

        return $query;
    }
}
