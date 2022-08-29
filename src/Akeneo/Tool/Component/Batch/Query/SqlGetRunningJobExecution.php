<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Connection;

class SqlGetRunningJobExecution
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getByJobCode(string $jobCode): array
    {
        $sql = <<<SQL
            SELECT job_execution.id, job_execution.status, job_execution.start_time, job_execution.updated_time
            FROM akeneo_batch_job_execution AS job_execution
            INNER JOIN akeneo_batch_job_instance AS job_instance ON job_instance.id = job_execution.job_instance_id
            WHERE job_instance.code = :job_code 
              AND exit_code IN (:exit_codes)
        SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['job_code' => $jobCode, 'exit_codes' => [ExitStatus::EXECUTING, ExitStatus::UNKNOWN]],
            ['job_code' => \PDO::PARAM_STR, 'exit_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        if (false === $result) {
            return [];
        }

        return $result;
    }
}
