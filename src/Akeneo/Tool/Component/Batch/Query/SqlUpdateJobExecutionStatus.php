<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

class SqlUpdateJobExecutionStatus
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function updateByJobExecutionId(int $jobExecutionId, BatchStatus $batchStatus): void
    {
        $sql = <<<SQL
UPDATE akeneo_batch_job_execution
SET `status` = :batch_status, `exit_code` = :exit_code
WHERE `id` = :job_execution_id
SQL;
        $this->connection->executeUpdate(
            $sql,
            [
                'batch_status'     => $batchStatus->getValue(),
                'exit_code'        => $batchStatus->__toString(),
                'job_execution_id' => $jobExecutionId
            ],
            [
                'batch_status'     => \PDO::PARAM_INT,
                'exit_code'        => \PDO::PARAM_STR,
                'job_execution_id' => \PDO::PARAM_INT
            ]
        );
    }
}
