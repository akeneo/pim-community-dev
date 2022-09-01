<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Infrastructure\Storage;

use Akeneo\UserManagement\Domain\Model\UserRole;
use Akeneo\UserManagement\Domain\Storage\FindAllUserRoles;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindAllUserRoles implements FindAllUserRoles
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
    public function __invoke(): array
    {
        $query = <<<SQL
            SELECT
                `id`,
                `role`,
                `label`,
                `type`
            FROM oro_access_role
        SQL;

        $results = $this->connection->executeQuery($query)->fetchAllAssociative();

        return array_map(static fn (array $data) => UserRole::createFromDatabase($data), $results);
    }
}
