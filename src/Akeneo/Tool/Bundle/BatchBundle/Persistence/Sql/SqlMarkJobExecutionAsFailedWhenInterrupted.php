<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Query\MarkJobExecutionAsFailedWhenInterrupted;
use Doctrine\DBAL\Connection;

final class SqlMarkJobExecutionAsFailedWhenInterrupted implements MarkJobExecutionAsFailedWhenInterrupted
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $jobCodes): int
    {
        $sql = <<<SQL
UPDATE akeneo_batch_job_execution job_execution
INNER JOIN akeneo_batch_job_instance job_instance ON job_execution.job_instance_id = job_instance.id
SET job_execution.status = :failedStatus, job_execution.exit_code = :failedExitCode
WHERE job_instance.code IN (:jobCodes)
AND job_execution.health_check_time IS NULL
AND job_execution.status IN (:runningStatuses);
SQL;

        return $this->connection->executeUpdate(
            $sql,
            [
                'jobCodes' => $jobCodes,
                'failedStatus' => BatchStatus::FAILED,
                'failedExitCode' => ExitStatus::FAILED,
                'runningStatuses' => [BatchStatus::STARTED, BatchStatus::STOPPING],
            ],
            [
                'jobCodes' => Connection::PARAM_INT_ARRAY,
                'runningStatuses' => Connection::PARAM_INT_ARRAY,
            ]
        );
    }
}
