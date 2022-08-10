<?php

namespace Akeneo\UserManagement\Infrastructure\Storage;

use Akeneo\UserManagement\Application\Storage\FindUserGroups;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class SqlFindUserGroups implements FindUserGroups
{
    public function __construct(
        private Connection $connection
    ) {

    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        int $limit = self::DEFAULT_LIMIT,
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
SELECT id, name 
FROM oro_access_group oag
WHERE oag.type = 'default'
${searchSql}
${searchAfterIdSql}
ORDER BY oag.id
${limitSql} 
SQL;
        return $this->connection->executeQuery(
            $query
        )->fetchAllAssociative();
    }
}
