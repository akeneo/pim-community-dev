<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobExecution implements SearchJobExecutionInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function search(SearchJobExecutionQuery $query): array
    {
        $sql = <<<SQL
    SELECT
        je.id,
        ji.label,
        ji.type,
        je.start_time,
        je.user,
        je.status,
        SUM(IFNULL(se.warning_count, 0)) as warning_count,
           je.tra
    FROM akeneo_batch_job_execution je
    JOIN akeneo_batch_job_instance ji on je.job_instance_id = ji.id
    LEFT JOIN akeneo_batch_step_execution se on je.id = se.job_execution_id
    GROUP BY je.id, ji.label, ji.type, je.start_time, je.user, je.status
    ORDER BY ISNULL(je.start_time) DESC, je.start_time DESC
    LIMIT :offset, :limit;
SQL;

        $page = $query->page;
        $size = $query->size;

        $rawJobExecutions = $this->connection->executeQuery(
            $sql,
            [
                'offset' => ($page - 1) * $size,
                'limit' => $size,
            ],
            [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ]
        )->fetchAll(\PDO::FETCH_ASSOC);

        return $this->buildJobExecutionRows($rawJobExecutions);
    }

    public function count(SearchJobExecutionQuery $query): int
    {
        $sql = <<<SQL
    SELECT count(*)
    FROM akeneo_batch_job_execution je
    JOIN akeneo_batch_job_instance ji on je.job_instance_id = ji.id
SQL;

        return (int) $this->connection->executeQuery($sql)->fetchColumn();
    }

    private function buildJobExecutionRows(array $rawJobExecutions): array
    {
        return array_map(
            static fn ($rawJobExecution) =>
                new JobExecutionRow(
                    (int) $rawJobExecution['id'],
                    $rawJobExecution['label'],
                    $rawJobExecution['type'],
                    $rawJobExecution['start_time'],
                    $rawJobExecution['user'],
                    (string) new BatchStatus((int) $rawJobExecution['status']),
                    (int) $rawJobExecution['warning_count'],
                ),
            $rawJobExecutions
        );
    }
}
