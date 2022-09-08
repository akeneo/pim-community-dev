<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Infrastructure\Hydrator\JobExecutionRowHydrator;
use Doctrine\DBAL\Connection;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobExecution implements SearchJobExecutionInterface
{
    private const SEARCH_PART_PARAM_SUFFIX = 'search_part';

    public function __construct(
        private Connection $connection,
        private JobExecutionRowHydrator $jobExecutionRowHydrator,
    ) {
    }

    public function search(SearchJobExecutionQuery $query): array
    {
        $sql = $this->buildSqlQuery($query);

        return $this->fetchJobExecutionRows($sql, $query);
    }

    public function count(SearchJobExecutionQuery $query): int
    {
        $sql = <<<SQL
    SELECT count(*)
    FROM akeneo_batch_job_execution job_execution
    JOIN akeneo_batch_job_instance job_instance on job_execution.job_instance_id = job_instance.id
    WHERE job_execution.is_visible = 1
    %s
SQL;
        $whereSqlPart = $this->buildSqlWherePart($query);

        $sql = sprintf($sql, $whereSqlPart);
        $queryParams = $this->buildQueryParams($query);
        $queryParamsTypes = $this->buildQueryParamsTypes();

        return (int) $this->connection->executeQuery(
            $sql,
            $queryParams,
            $queryParamsTypes,
        )->fetchOne();
    }

    private function buildSqlQuery(SearchJobExecutionQuery $query): string
    {
        $sql = <<<SQL
    WITH job_executions AS (
        SELECT
            job_execution.id,
            job_execution.job_instance_id,
            job_instance.label,
            job_instance.type,
            job_execution.start_time,
            job_execution.user,
            job_execution.is_stoppable,
            job_execution.step_count,
            %s AS calculated_status
        FROM akeneo_batch_job_execution job_execution
        JOIN akeneo_batch_job_instance job_instance ON job_execution.job_instance_id = job_instance.id
        WHERE job_execution.is_visible = 1
        %s
        %s
        LIMIT :offset, :limit
    )
    SELECT
        job_execution.*,
        COUNT(step_execution.job_execution_id) AS current_step_number,
        JSON_ARRAYAGG(JSON_OBJECT(
            'id', step_execution.id,
            'start_time', step_execution.start_time,
            'end_time', step_execution.end_time,
            'warning_count', step_execution.warning_count,
            'has_error', IF(IFNULL(step_execution.failure_exceptions, 'a:0:{}') <> 'a:0:{}' OR IFNULL(step_execution.errors, 'a:0:{}') <> 'a:0:{}', 1, 0),
            'total_items', JSON_EXTRACT(step_execution.tracking_data, '$.totalItems'),
            'processed_items', JSON_EXTRACT(step_execution.tracking_data, '$.processedItems'),
            'status', step_execution.status,
            'is_trackable', step_execution.is_trackable
        )) AS steps
    FROM job_executions job_execution
    LEFT JOIN akeneo_batch_step_execution step_execution ON job_execution.id = step_execution.job_execution_id
    GROUP BY job_execution.id
    %s
SQL;

        $whereSqlPart = $this->buildSqlWherePart($query);
        $orderBySqlPart = $this->buildSqlOrderByPart($query);

        return sprintf($sql, $this->buildStatusSubQuery(), $whereSqlPart, $orderBySqlPart, str_replace('job_instance', 'job_execution', $orderBySqlPart));
    }

    private function buildSqlWherePart(SearchJobExecutionQuery $query): string
    {
        $sqlWhereParts = [];
        $automation = $query->automation;
        $type = $query->type;
        $status = $query->status;
        $user = $query->user;
        $search = $query->search;
        $code = $query->code;

        if (null !== $automation) {
            $sqlWhereParts[] = $automation ? 'job_execution.user LIKE "job_automated%"' : 'job_execution.user NOT LIKE "job_automated%"';
        }

        if (!empty($type)) {
            $sqlWhereParts[] = 'job_instance.type IN (:type)';
        }

        if (!empty($code)) {
            $sqlWhereParts[] = 'job_instance.code IN (:code)';
        }

        if (!empty($status)) {
            $sqlWhereParts[] = $this->buildStatusSubQuery().' IN (:status)';
        }

        if (!empty($user)) {
            $sqlWhereParts[] = 'job_execution.user IN (:user)';
        }

        if (!empty($search)) {
            $searchParts = explode(' ', $search);
            foreach (array_keys($searchParts) as $index) {
                $sqlWhereParts[] = sprintf('job_instance.label LIKE :%s_%s', self::SEARCH_PART_PARAM_SUFFIX, $index);
            }
        }

        return empty($sqlWhereParts) ? '' : 'AND '.implode(' AND ', $sqlWhereParts);
    }

    private function buildSqlOrderByPart(SearchJobExecutionQuery $query): string
    {
        $sortDirection = $query->sortDirection;

        $orderByColumn = match ($query->sortColumn) {
            'job_name' => sprintf('job_instance.label %s', $sortDirection),
            'type' => sprintf('job_instance.type %s', $sortDirection),
            'started_at' => sprintf('job_execution.start_time %s', $sortDirection),
            'username' => sprintf('job_execution.user %s', $sortDirection),
            'status' => sprintf('calculated_status %s', $sortDirection),
            default => throw new \InvalidArgumentException(sprintf('Unknown sort column "%s"', $query->sortColumn)),
        };

        return sprintf('ORDER BY %s', $orderByColumn);
    }

    /**
     * this query part is here to prevent wrong status from being displayed when a job has been interrupted
     * by a major crash (deamon failing, sql pod error during process etc...). Since such error is not manageable
     * we got job with an non updated status in db (mostly IN_PROGRESS).
     */
    private function buildStatusSubQuery(): string
    {
        return 'CASE TIMESTAMPDIFF(SECOND, job_execution.health_check_time, CURRENT_TIME()) > 10
                AND job_execution.status in (:starting_status_code, :in_progress_status_code, :stopping_status_code)
                AND job_execution.health_check_time is not null
                WHEN TRUE THEN :failed_status_code ELSE job_execution.status
                END';
    }

    private function fetchJobExecutionRows(string $sql, SearchJobExecutionQuery $query): array
    {
        $queryParams = $this->buildQueryParams($query);
        $queryParamsTypes = $this->buildQueryParamsTypes();

        $page = $query->page;
        $size = $query->size;

        $jobExecutions = $this->connection->executeQuery(
            $sql,
            array_merge($queryParams, [
                'offset' => ($page - 1) * $size,
                'limit' => $size,
            ]),
            array_merge($queryParamsTypes, [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ]),
        )->fetchAllAssociative();

        return array_map(
            fn ($jobExecution): JobExecutionRow => $this->jobExecutionRowHydrator->hydrate($jobExecution),
            $jobExecutions,
        );
    }

    private function buildQueryParams(SearchJobExecutionQuery $query): array
    {
        $queryParams = [
            'type' => $query->type,
            'status' => array_map(static fn (string $status) => Status::fromLabel($status)->getStatus(), $query->status),
            'user' => $query->user,
            'code' => $query->code,
            'starting_status_code' => Status::STARTING,
            'in_progress_status_code' => Status::IN_PROGRESS,
            'stopping_status_code' => Status::STOPPING,
            'failed_status_code' => Status::FAILED,
        ];

        $searchParts = explode(' ', $query->search);
        foreach ($searchParts as $index => $searchPart) {
            $searchPartName = sprintf('%s_%s', self::SEARCH_PART_PARAM_SUFFIX, $index);
            $queryParams[$searchPartName] = sprintf('%%%s%%', $searchPart);
        }

        return $queryParams;
    }

    private function buildQueryParamsTypes(): array
    {
        return [
            'type' => Connection::PARAM_STR_ARRAY,
            'status' => Connection::PARAM_STR_ARRAY,
            'user' => Connection::PARAM_STR_ARRAY,
            'code' => Connection::PARAM_STR_ARRAY,
        ];
    }
}
