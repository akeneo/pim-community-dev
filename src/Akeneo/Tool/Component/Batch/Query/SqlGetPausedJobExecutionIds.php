<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

class SqlGetPausedJobExecutionIds
{
    public function __construct(private readonly Connection $connection)
    {}

    /**
     * @return array<int>
     */
    public function all(): array
    {
        $sql = <<<SQL
SELECT id
FROM akeneo_batch_job_execution AS job_execution
WHERE job_execution.status = :batch_status
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['batch_status' => BatchStatus::PAUSED],
        )->fetchFirstColumn();

        return array_map(static fn(string $id) => (int) $id, $result);
    }

}