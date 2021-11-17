<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Infrastructure\Registry\NotVisibleJobsRegistry;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobExecution implements SearchJobExecutionInterface
{
    const SEARCH_PART_PARAM_SUFFIX = 'search_part';

    private Connection $connection;
    private NotVisibleJobsRegistry $notVisibleJobsRegistry;

    public function __construct(Connection $connection, NotVisibleJobsRegistry $notVisibleJobsRegistry)
    {
        $this->connection = $connection;
        $this->notVisibleJobsRegistry = $notVisibleJobsRegistry; #TODO RAC-1013
    }

    public function search(SearchJobExecutionQuery $query): array
    {
        $notVisibleJobsCodes = $this->notVisibleJobsRegistry->getCodes();

        $sql = <<<SQL
    WITH job_executions AS (
        SELECT
            je.id,
            je.job_instance_id,
            ji.label,
            ji.type,
            je.start_time,
            je.user,
            je.status
        FROM akeneo_batch_job_execution je
        JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id
        WHERE ji.code NOT IN (:not_visible_jobs_codes)
        %s
        %s
        LIMIT :offset, :limit
    )

    SELECT
       je.*,
       SUM(IFNULL(se.warning_count, 0)) AS warning_count,
       COUNT(se.job_execution_id) AS current_step_number,
       JSON_MERGE(
            JSON_ARRAYAGG(IFNULL(se.failure_exceptions, 'a:0:{}')),
            JSON_ARRAYAGG(IFNULL(se.errors, 'a:0:{}'))
      ) as errors
    FROM job_executions je
    LEFT JOIN akeneo_batch_step_execution se ON je.id = se.job_execution_id
    GROUP BY je.id
    %s
SQL;

        $whereSqlPart = $this->buildSqlWherePart($query);
        $orderBySqlPart = $this->buildSqlOrderByPart($query);

        $sql = sprintf($sql, $whereSqlPart, $orderBySqlPart, str_replace('ji', 'je', $orderBySqlPart));
        $queryParams = $this->buildQueryParams($query);
        $queryParamsTypes = $this->buildQueryParamsTypes();

        $page = $query->page;
        $size = $query->size;

        $rawJobExecutions = $this->connection->executeQuery(
            $sql,
            array_merge($queryParams, [
                'not_visible_jobs_codes' => $notVisibleJobsCodes,
                'offset' => ($page - 1) * $size,
                'limit' => $size,
            ]),
            array_merge($queryParamsTypes, [
                'not_visible_jobs_codes' => Connection::PARAM_STR_ARRAY,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ]),
        )->fetchAllAssociative();

        return $this->buildJobExecutionRows($rawJobExecutions);
    }

    public function count(SearchJobExecutionQuery $query): int
    {
        $sql = <<<SQL
    SELECT count(*)
    FROM akeneo_batch_job_execution je
    JOIN akeneo_batch_job_instance ji on je.job_instance_id = ji.id
    %s
SQL;
        $whereSqlPart = $this->buildSqlWherePart($query);

        $sql = sprintf($sql, $whereSqlPart);
        $queryParams = $this->buildQueryParams($query);
        $queryParamsTypes = $this->buildQueryParamsTypes();

        return (int) $this->connection->executeQuery(
            $sql,
            $queryParams,
            $queryParamsTypes
        )->fetchOne();
    }

    private function buildSqlWherePart(SearchJobExecutionQuery $query): string
    {
        $sqlWhereParts = [];
        $type = $query->type;
        $status = $query->status;
        $search = $query->search;

        if (!empty($type)) {
            $sqlWhereParts[] = 'ji.type IN (:type)';
        }

        if (!empty($status)) {
            $sqlWhereParts[] = 'je.status IN (:status)';
        }

        if (!empty($search)) {
            $searchParts = explode(' ', $search);
            foreach ($searchParts as $index => $searchPart) {
                $sqlWhereParts[] = sprintf('ji.label LIKE :%s_%s', self::SEARCH_PART_PARAM_SUFFIX, $index);
            }
        }

        return empty($sqlWhereParts) ? '' : 'AND ' . implode(' AND ', $sqlWhereParts);
    }

    private function buildQueryParams(SearchJobExecutionQuery $query): array
    {
        $statusLabels = BatchStatus::getAllLabels();

        $queryParams = [
            'type' => $query->type,
            'status' => array_map(static fn (string $status) => $statusLabels[$status], $query->status),
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
        ];
    }

    private function buildSqlOrderByPart(SearchJobExecutionQuery $query): string
    {
        $sortDirection = $query->sortDirection;

        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException(sprintf('Sort direction "%s" is not supported', $query->sortDirection));
        }

        switch ($query->sortColumn) {
            case 'job_name':
                $orderByColumn = "ji.label $sortDirection";
                break;
            case 'type':
                $orderByColumn = "ji.type $sortDirection";
                break;
            case 'started_at':
                $orderByColumn = "ISNULL(je.start_time) $sortDirection, je.start_time $sortDirection";
                break;
            case 'username':
                $orderByColumn = "je.user $sortDirection";
                break;
            case 'status':
                $orderByColumn = "je.status $sortDirection";
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Sort column "%s" is not supported', $query->sortColumn));
        }

        return sprintf('ORDER BY %s', $orderByColumn);
    }

    private function buildJobExecutionRows(array $rawJobExecutions): array
    {
        $platform = $this->connection->getDatabasePlatform();

        return array_map(function (array $rawJobExecution) use ($platform): JobExecutionRow {
            $errors = json_decode($rawJobExecution['errors'], true); // TODO revalidate that currently the errors are here
            $errorCount = 0;
            foreach ($errors as $error) {
                $errorCount += count(unserialize($error));
            }

            $startTime = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($rawJobExecution['start_time'], $platform);

            return new JobExecutionRow(
                (int) $rawJobExecution['id'],
                $rawJobExecution['label'],
                $rawJobExecution['type'],
                $startTime,
                $rawJobExecution['user'],
                (string) new BatchStatus((int) $rawJobExecution['status']),
                (int) $rawJobExecution['warning_count'],
                $errorCount,
                (int) $rawJobExecution['current_step_number'] ?? 0,
                3 #TODO RAC-1009
            );
        }, $rawJobExecutions);
    }
}
