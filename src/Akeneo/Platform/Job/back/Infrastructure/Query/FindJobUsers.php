<?php

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface;
use Akeneo\Platform\Job\Infrastructure\Registry\NotVisibleJobsRegistry;
use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobUsers implements FindJobUsersInterface
{
    const USER_PER_PAGE = 10;

    private Connection $connection;
    private NotVisibleJobsRegistry $notVisibleJobsRegistry;

    public function __construct(Connection $connection, NotVisibleJobsRegistry $notVisibleJobsRegistry)
    {
        $this->connection = $connection;
        $this->notVisibleJobsRegistry = $notVisibleJobsRegistry; #TODO RAC-1013
    }

    public function visible(int $page): array
    {
        $notVisibleJobsCodes = $this->notVisibleJobsRegistry->getCodes();

        $sql = <<<SQL
SELECT DISTINCT je.user
FROM akeneo_batch_job_execution je
JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id
WHERE ji.code NOT IN (:not_visible_jobs_codes)
ORDER BY je.user
LIMIT :offset, :limit
SQL;

        $jobUsers = $this->connection->executeQuery(
            $sql,
            [
                'not_visible_jobs_codes' => $notVisibleJobsCodes,
                'offset' => ($page - 1) * self::USER_PER_PAGE,
                'limit' => self::USER_PER_PAGE,
            ],
            [
                'not_visible_jobs_codes' => Connection::PARAM_STR_ARRAY,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchFirstColumn();

        return $jobUsers;
    }
}
