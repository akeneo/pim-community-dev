<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\Infrastructure\Storage;

use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Domain\Model\User as ServiceApiUser;
use Akeneo\UserManagement\Domain\Storage\FindUsers;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception as DBALException;

final class SqlFindUsers implements FindUsers
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @throws DriverException
     * @throws DBALException
     */
    public function __invoke(
        ?string $search = null,
        ?int $searchAfterId = null,
        ?array $includeIds = null,
        ?array $includeGroupIds = null,
        int $limit = self::DEFAULT_LIMIT,
    ): array {
        $query = $this->buildQuery($search, $searchAfterId, $includeIds, $includeGroupIds, $limit);

        $results = $this->connection->executeQuery(
            $query,
            [
                'search' => sprintf('%%%s%%', $search),
                'searchAfterId' => $searchAfterId,
                'includeIds' => $includeIds,
                'includeGroupIds' => $includeGroupIds,
            ],
            [
                'includeIds' => Connection::PARAM_STR_ARRAY,
                'includeGroupIds' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAllAssociative();

        return array_map(
            static fn ($data) => ServiceApiUser::createFromDatabase($data),
            $results
        );
    }

    private function buildQuery(?string $search, ?int $searchAfterId, ?array $includeIds, ?array $includeGroupIds, ?int $limit): string
    {
        $sqlWhereParts = [];
        $sqlLimitPart = '';

        if (null !== $search) {
            $sqlWhereParts[] = '(ou.username LIKE :search OR ou.email LIKE :search OR ou.first_name LIKE :search OR ou.last_name LIKE :search)';
        }

        if (null !== $searchAfterId) {
            $sqlWhereParts[] = 'ou.id > :searchAfterId';
        }

        if (null !== $includeIds) {
            $sqlWhereParts[] = 'ou.id IN (:includeIds)';
        }

        if (null !== $includeGroupIds) {
            $sqlWhereParts[] = 'ou.id IN (SELECT user_id FROM oro_user_access_group WHERE group_id IN (:includeGroupIds))';
        }

        if (null !== $limit) {
            $sqlLimitPart = sprintf('LIMIT %s', $limit);
        }

        $sqlWhereParts = empty($sqlWhereParts) ? '' : 'AND '.implode(' AND ', $sqlWhereParts);

        $type = User::TYPE_USER;

        return <<<SQL
            SELECT id, email, username, user_type, first_name, last_name, middle_name, name_suffix, image
            FROM oro_user as ou
            WHERE ou.user_type='{$type}'
            {$sqlWhereParts}
            ORDER BY ou.id
            {$sqlLimitPart}
        SQL;
    }
}
