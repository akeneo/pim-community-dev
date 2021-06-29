<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Storage\Query;

use Akeneo\UserManagement\Component\Query\PublicApi\IsCategoryTreeLinkedToUser;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlIsCategoryTreeLinkedToUser implements IsCategoryTreeLinkedToUser
{
    private Connection $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function byCategoryTreeId(int $categoryTreeId): bool
    {
        $sql = <<<SQL
        SELECT EXISTS (
            SELECT *
            FROM oro_user
            WHERE defaultTree_id = :treeId
        )
        SQL;

        $exists = $this->connection->executeQuery(
            $sql,
            [
                'treeId' => $categoryTreeId
            ]
        )->fetchColumn();

        return (bool) $exists;
    }
}
