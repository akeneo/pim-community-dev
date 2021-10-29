<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecutionTable\JobExecutionRow;
use Akeneo\Platform\Job\Domain\Query\FindJobExecutionRowsForQueryInterface;
use Akeneo\Platform\Job\Domain\Query\SearchExecutionTableQueryInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobExecutionRowsForQuery implements FindJobExecutionRowsForQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(SearchExecutionTableQueryInterface $query): array
    {
        $sql = <<<SQL
    SELECT
        ji.label as job,
        ji.type,
        je.start_time as start_at,
        je.user as username,
        je.status,
        SUM(se.warning_count) as warning_count
    FROM akeneo_batch_job_execution je
    JOIN akeneo_batch_job_instance ji on je.job_instance_id = ji.id
    JOIN akeneo_batch_step_execution se on je.id = se.job_execution_id
    GROUP BY je.id;
SQL;

        $rawJobExecutions = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $this->buildJobExecutionRows($rawJobExecutions);
    }

    private function buildJobExecutionRows(array $rawJobExecutions): array
    {
        return array_map(
            static fn ($rawJobExecution) =>
                new JobExecutionRow(
                    $rawJobExecution['job'],
                    $rawJobExecution['type'],
                    $rawJobExecution['start_time'],
                    $rawJobExecution['username'],
                    (string) new BatchStatus((int) $rawJobExecution['status']),
                    (int) $rawJobExecution['warning_count'],
                ),
            $rawJobExecutions
        );
    }
}
