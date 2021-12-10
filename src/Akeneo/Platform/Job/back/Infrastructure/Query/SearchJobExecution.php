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
    FROM akeneo_batch_job_execution je
    JOIN akeneo_batch_job_instance ji on je.job_instance_id = ji.id
    WHERE je.is_visible = 1
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
            je.id,
            je.job_instance_id,
            ji.label,
            ji.type,
            je.start_time,
            je.user,
            je.status,
            je.is_stoppable,
            je.step_count
        FROM akeneo_batch_job_execution je
        JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id
        WHERE je.is_visible = 1
        %s
        %s
        LIMIT :offset, :limit
    )
    SELECT
        je.*,
        COUNT(se.job_execution_id) AS current_step_number,
        JSON_ARRAYAGG(JSON_OBJECT(
            'id', se.id,
            'start_time', se.start_time,
            'end_time', se.end_time,
            'warning_count', se.warning_count,
            'errors', JSON_ARRAY(IFNULL(se.failure_exceptions, 'a:0:{}'), IFNULL(se.errors, 'a:0:{}')),
            'total_items', JSON_EXTRACT(se.tracking_data, '$.totalItems'),
            'processed_items', JSON_EXTRACT(se.tracking_data, '$.processedItems'),
            'status', se.status,
            'is_trackable', se.is_trackable
        )) AS steps
    FROM job_executions je
    LEFT JOIN akeneo_batch_step_execution se ON je.id = se.job_execution_id
    GROUP BY je.id
    %s
SQL;

        $whereSqlPart = $this->buildSqlWherePart($query);
        $orderBySqlPart = $this->buildSqlOrderByPart($query);

        return sprintf($sql, $whereSqlPart, $orderBySqlPart, str_replace('ji', 'je', $orderBySqlPart));
    }

    private function buildSqlWherePart(SearchJobExecutionQuery $query): string
    {
        $sqlWhereParts = [];
        $type = $query->type;
        $status = $query->status;
        $user = $query->user;
        $search = $query->search;
        $code = $query->code;

        if (!empty($type)) {
            $sqlWhereParts[] = 'ji.type IN (:type)';
        }

        if (!empty($code)) {
            $sqlWhereParts[] = 'ji.code IN (:code)';
        }

        if (!empty($status)) {
            $sqlWhereParts[] = 'je.status IN (:status)';
        }

        if (!empty($user)) {
            $sqlWhereParts[] = 'je.user IN (:user)';
        }

        if (!empty($search)) {
            $searchParts = explode(' ', $search);
            foreach ($searchParts as $index => $searchPart) {
                $sqlWhereParts[] = sprintf('ji.label LIKE :%s_%s', self::SEARCH_PART_PARAM_SUFFIX, $index);
            }
        }

        return empty($sqlWhereParts) ? '' : 'AND ' . implode(' AND ', $sqlWhereParts);
    }

    private function buildSqlOrderByPart(SearchJobExecutionQuery $query): string
    {
        $sortDirection = $query->sortDirection;

        $orderByColumn = match ($query->sortColumn) {
            'job_name' => sprintf("ji.label %s", $sortDirection),
            'type' => sprintf("ji.type %s", $sortDirection),
            'started_at' => sprintf("je.start_time %s", $sortDirection),
            'username' => sprintf("je.user %s", $sortDirection),
            'status' => sprintf("je.status %s", $sortDirection),
            default => throw new \InvalidArgumentException(sprintf('Unknown sort column "%s"', $query->sortColumn)),
        };

        return sprintf('ORDER BY %s', $orderByColumn);
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
