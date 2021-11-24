<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\FindJobUsersQuery;
use Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobUsers implements FindJobUsersInterface
{
    private const USER_PER_PAGE = 10;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function search(FindJobUsersQuery $query): array
    {
        $page = $query->page;
        $size = $query->size;
        $username = $query->username;

        $sql = <<<SQL
SELECT DISTINCT job_execution.user
FROM akeneo_batch_job_execution job_execution
WHERE job_execution.is_visible = 1
%s
ORDER BY job_execution.user
LIMIT :offset, :limit
SQL;

        $wherePart = '';

        if (!empty($username)) {
            $wherePart = 'AND job_execution.user LIKE :username';
        }

        $sql = sprintf($sql, $wherePart);

        $jobUsers = $this->connection->executeQuery(
            $sql,
            [
                'offset' => ($page - 1) * $size,
                'limit' => $size,
                'username' => sprintf('%%%s%%', $username),
            ],
            [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchFirstColumn();

        return $jobUsers;
    }
}
