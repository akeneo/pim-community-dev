<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\ClockInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRowTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Application\SearchJobExecution\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * TODO: RAC-1103
 * Should we extract this class into multiple classes ?
 *  - To manage filters: instead of managing both at the same time in private classes buildQueryParams and buildQueryParamsTypes ?
 *      - TypeFilter class: responsible for handling type filter
 *      - StatusFilter class: responsible for handling the Status filtering
 *  - To manage JobExecutionRow hydration in a dedicated class too (if possible)
 *
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobExecution implements SearchJobExecutionInterface
{
    private const SEARCH_PART_PARAM_SUFFIX = 'search_part';

    public function __construct(
        private Connection $connection,
        private ClockInterface $clock
    ) {
    }

    public function search(SearchJobExecutionQuery $query): array
    {
        // TODO: RAC-1102
        // I'd split this public into 2 private for clarity,
        // the body of this function becomes:
        // $sql = $this->buildSqlQuery($query);
        // return $this->fetchJobExecutionRow($sql);

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
        SUM(IFNULL(se.warning_count, 0)) AS warning_count,
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

        $sql = sprintf($sql, $whereSqlPart, $orderBySqlPart, str_replace('ji', 'je', $orderBySqlPart));
        $queryParams = $this->buildQueryParams($query);
        $queryParamsTypes = $this->buildQueryParamsTypes();

        $page = $query->page;
        $size = $query->size;

        $rawJobExecutions = $this->connection->executeQuery(
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

        return $this->buildJobExecutionRows($rawJobExecutions);
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

    private function buildJobExecutionRows(array $rawJobExecutions): array
    {
        $platform = $this->connection->getDatabasePlatform();

        return array_map(function (array $rawJobExecution) use ($platform): JobExecutionRow {
            $startTime = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($rawJobExecution['start_time'], $platform);
            $tracking = $this->buildTracking($rawJobExecution);

            return new JobExecutionRow(
                (int) $rawJobExecution['id'],
                $rawJobExecution['label'],
                $rawJobExecution['type'],
                $startTime,
                $rawJobExecution['user'],
                Status::fromStatus((int) $rawJobExecution['status']),
                (int) $rawJobExecution['warning_count'],
                $tracking->getErrorCount(),
                (bool) $rawJobExecution['is_stoppable'],
                $tracking,
            );
        }, $rawJobExecutions);
    }

    private function buildTracking(array $rawJobExecution): JobExecutionRowTracking
    {
        $currentStepNumber = (int) $rawJobExecution['current_step_number'] ?? 0;
        $stepCount = (int) $rawJobExecution['step_count'];

        if (0 === $currentStepNumber) {
            return new JobExecutionRowTracking(
                $currentStepNumber,
                $stepCount,
                [],
            );
        }

        $steps = array_map(function ($step) {
            $startTime = $step['start_time'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $step['start_time']) : null;
            $endTime = $step['end_time'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $step['end_time']) : null;
            $errorCount = array_reduce(
                $step['errors'],
                static fn (int $errorCount, string $error) => $errorCount + count(unserialize($error)),
                0,
            );
            $status = Status::fromStatus((int) $step['status']);
            $duration = $this->computeDuration($status, $startTime, $endTime);

            return new StepExecutionTracking(
                (int) $step['id'],
                $duration,
                (int) $step['warning_count'],
                $errorCount,
                (int) $step['total_items'],
                (int) $step['processed_items'],
                (bool) $step['is_trackable'],
                $status,
            );
        }, json_decode($rawJobExecution['steps'], true));

        usort(
            $steps,
            static fn (StepExecutionTracking $step1, StepExecutionTracking $step2) => $step1->getId() <=> $step2->getId(),
        );

        return new JobExecutionRowTracking(
            $currentStepNumber,
            $stepCount,
            $steps,
        );
    }

    private function computeDuration(Status $status, ?\DateTimeImmutable $startTime, ?\DateTimeImmutable $endTime): int
    {
        $now = $this->clock->now();
        if ($status->getStatus() === Status::STARTING || null === $startTime) {
            return 0;
        }

        $duration = $now->getTimestamp() - $startTime->getTimestamp();
        if (null !== $endTime) {
            $duration = $endTime->getTimestamp() - $startTime->getTimestamp();
        }

        return $duration;
    }
}
