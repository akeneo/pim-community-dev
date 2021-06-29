<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

class SqlGetJobExecutionStatus implements GetJobExecutionStatusInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getByJobExecutionId(int $jobExecutionId): ?BatchStatus
    {
        $sql = <<<SQL
SELECT status
FROM akeneo_batch_job_execution AS job_execution
WHERE job_execution.id = :job_execution_id
SQL;
        $result = $this->connection->executeQuery(
            $sql,
            ['job_execution_id' => $jobExecutionId],
            ['job_execution_id' => \PDO::PARAM_INT]
        )->fetch();

        if (false === $result) {
            return null;
        }

        return new BatchStatus((int) $result['status']);
    }
}
