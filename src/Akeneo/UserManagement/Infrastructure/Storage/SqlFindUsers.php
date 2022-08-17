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
use Akeneo\UserManagement\Domain\Model\User as ServiceAPiUser;
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
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $query = $this->buildQuery($search, $limit, $offset);

        $results = $this->connection->executeQuery($query)->fetchAllAssociative();

        return array_map(
            static function ($data) {
                return ServiceAPiUser::createFromDatabase($data);
            },
            $results
        );
    }

    private function buildQuery(?string $search, ?int $limit, ?int $offset): string
    {
        $searchSql = '';
        if (null !== $search) {
            $searchSql = <<<SQL
                AND (ou.username LIKE '%${search}%' OR ou.email LIKE '%${search}%' OR ou.first_name LIKE '%${search}%' OR ou.last_name LIKE '%${search}%')
            SQL;
        }

        $limitSql = '';
        if (null !== $limit) {
            if (null !== $offset) {
                $limitSql = <<<SQL
                    LIMIT ${limit},${offset}
                SQL;
            } else {
                $limitSql = <<<SQL
                    LIMIT ${limit}
                SQL;
            }
        }

        $type = User::TYPE_USER;

        return <<<SQL
            SELECT id, email, username, user_type, first_name, last_name, middle_name, name_suffix, image 
            FROM oro_user as ou
            WHERE ou.user_type=${type}
            ${searchSql}
            ORDER BY ou.id
            ${limitSql} 
        SQL;
    }
}
