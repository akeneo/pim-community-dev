<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Storage\Query;

use Akeneo\UserManagement\Component\Storage\Query\GetUserCountInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetUserCount implements GetUserCountInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forUsersHavingOnlyRole(string $role): int
    {
        $sql = <<<SQL
WITH
user_with_role AS (
    SELECT u.id, u.username
    FROM oro_user u
        JOIN oro_user_access_role ur ON u.id = ur.user_id
        JOIN oro_access_role r ON ur.role_id = r.id AND r.role = :role
),
user_with_only_role AS (
    SELECT uwr.username
    FROM user_with_role uwr
        JOIN oro_user_access_role r ON uwr.id = r.user_id
    GROUP BY uwr.username
    HAVING COUNT(r.role_id) = 1
)
SELECT count(username) FROM user_with_only_role
SQL;

        return (int) $this->connection->executeQuery($sql, ['role' => $role])->fetchColumn();
    }
}
