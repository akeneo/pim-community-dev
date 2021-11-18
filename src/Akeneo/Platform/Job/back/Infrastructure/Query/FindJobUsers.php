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
    private Connection $connection;
    private NotVisibleJobsRegistry $notVisibleJobsRegistry;

    public function __construct(Connection $connection, NotVisibleJobsRegistry $notVisibleJobsRegistry)
    {
        $this->connection = $connection;
        $this->notVisibleJobsRegistry = $notVisibleJobsRegistry; #TODO RAC-1013
    }

    public function visible(): array
    {
        $notVisibleJobsCodes = $this->notVisibleJobsRegistry->getCodes();

        $sql = <<<SQL
SELECT DISTINCT je.user
FROM akeneo_batch_job_execution je
JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id
WHERE ji.code NOT IN (:not_visible_jobs_codes);
SQL;

        $jobUsers = $this->connection->executeQuery(
            $sql,
            [
                'not_visible_jobs_codes' => $notVisibleJobsCodes,
            ],
            [
                'not_visible_jobs_codes' => Connection::PARAM_STR_ARRAY,
            ],
        )->fetchFirstColumn();

        return $jobUsers;
    }
}
