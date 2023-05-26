<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetPausedJobExecutionIds implements GetPausedJobExecutionIdsInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<int>
     */
    public function all(): array
    {
        $sql = <<<SQL
SELECT id
FROM akeneo_batch_job_execution
WHERE status = :paused_status
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['paused_status' => BatchStatus::PAUSED],
        )->fetchFirstColumn();

        return array_map(static fn (string $id) => (int) $id, $result);
    }
}
