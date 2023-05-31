<?php

namespace Akeneo\UserManagement\Infrastructure\Storage;

use Akeneo\UserManagement\Domain\Model\Group;
use Akeneo\UserManagement\Domain\Storage\FindUserGroups;
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
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        ?int $limit = self::DEFAULT_LIMIT,
    ): array {
        $query = $this->buildQuery($search, $searchAfterId, $limit);

        $results = $this->connection->executeQuery(
            $query,
            [
                'search' => sprintf('%%%s%%', $search),
                'searchAfterId' => $searchAfterId,
            ]
        )->fetchAllAssociative();

        return array_map(static fn ($data) => Group::createFromDatabase($data), $results);
    }

    private function buildQuery(
        ?string $search,
        ?int $searchAfterId,
        ?int $limit,
    ): string {
        $sqlWhereParts = [];
        $sqlLimitPart = '';

        if (null !== $search) {
            $sqlWhereParts[] = 'oag.name LIKE :search';
        }

        if (null !== $searchAfterId) {
            $sqlWhereParts[] = 'oag.id > :searchAfterId';
        }

        if (null !== $limit) {
            $sqlLimitPart = sprintf('LIMIT %s', $limit);
        }

        $sqlWherePart = empty($sqlWhereParts) ? '' : 'AND '.implode(' AND ', $sqlWhereParts);

        return <<<SQL
            SELECT
                oag.id,
                oag.name,
                oag.type,
                oag.default_permissions
            FROM oro_access_group oag
            WHERE oag.type = 'default'
            $sqlWherePart
            ORDER BY oag.id
            $sqlLimitPart
        SQL;
    }
}
