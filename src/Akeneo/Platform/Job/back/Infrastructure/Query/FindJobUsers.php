<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

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

    public function search(int $page): array
    {
        $sql = <<<SQL
SELECT DISTINCT job_execution.user
FROM akeneo_batch_job_execution job_execution
WHERE job_execution.is_visible = 1
ORDER BY job_execution.user
LIMIT :offset, :limit
SQL;

        $jobUsers = $this->connection->executeQuery(
            $sql,
            [
                'offset' => ($page - 1) * self::USER_PER_PAGE,
                'limit' => self::USER_PER_PAGE,
            ],
            [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchFirstColumn();

        return $jobUsers;
    }
}
