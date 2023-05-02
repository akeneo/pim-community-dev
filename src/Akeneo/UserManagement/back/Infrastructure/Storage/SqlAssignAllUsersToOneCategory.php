<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Infrastructure\Storage;

use Akeneo\UserManagement\Domain\Storage\AssignAllUsersToOneCategory;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlAssignAllUsersToOneCategory implements AssignAllUsersToOneCategory
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(int $categoryId): int
    {
        $sql = <<<SQL
            UPDATE oro_user SET defaultTree_id = :id;
        SQL;
        return $this->connection->executeQuery(
            $sql,
            ['id' => $categoryId],
            ['id' => PDO::PARAM_INT]
        )->rowCount();
    }
}
