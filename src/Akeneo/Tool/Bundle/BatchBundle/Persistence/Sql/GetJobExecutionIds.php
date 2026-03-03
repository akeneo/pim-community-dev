<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetJobExecutionIds
{
    public function __construct(private Connection $connection)
    {
    }

    public function olderThanDays(int $days, array $jobInstanceCodes, ?BatchStatus $status): Result
    {
        if ($days < 1) {
            throw new InvalidArgumentException(
                sprintf('Number of days should be strictly superior to 0, "%s given', $days)
            );
        }

        $endTime = new DateTime();
        $endTime->modify(sprintf('- %d days', $days));

        return $this->fetchOlderThanTime($endTime, $jobInstanceCodes, $status);
    }

    public function olderThanHours(int $hours, array $jobInstanceCodes, ?BatchStatus $status): Result
    {
        Assert::greaterThanEq(
            $hours,
            1,
            sprintf('Number of hours should be at least 1, "%s given', $hours)
        );

        $endTime = new DateTime();
        $endTime->modify(sprintf('- %d hours', $hours));

        return $this->fetchOlderThanTime($endTime, $jobInstanceCodes, $status);
    }

    public function all(array $jobInstanceCodes, ?BatchStatus $status): Result
    {
        $query = <<<SQL
            SELECT id
            FROM akeneo_batch_job_execution
        SQL;


        $conditions = [];
        if (!empty($jobInstanceCodes)) {
            $conditions[] = 'job_instance_id IN (
                SELECT ji.id
                FROM akeneo_batch_job_instance ji
                WHERE ji.code IN (:job_instance_codes)
            )';
        }

        if ($status !== null) {
            $conditions[] = 'status = :status_code';
        }

        $query .= empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);

        return $this->connection->executeQuery(
            $query,
            ['job_instance_codes' => $jobInstanceCodes, 'status_code' => $status?->getValue()],
            ['job_instance_codes' => Connection::PARAM_STR_ARRAY]
        );
    }

    private function fetchOlderThanTime(DateTime $timeLimit, array $jobInstanceCodes, ?BatchStatus $status): Result
    {
        $query = <<<SQL
            SELECT id
            FROM akeneo_batch_job_execution
            WHERE akeneo_batch_job_execution.create_time < :create_time AND akeneo_batch_job_execution.id NOT IN (
                SELECT MAX(last_job_execution.id) 
                FROM akeneo_batch_job_execution last_job_execution 
                WHERE last_job_execution.status = :status 
                GROUP BY last_job_execution.job_instance_id
            )
            %s
        SQL;

        $conditions = [];
        if (!empty($jobInstanceCodes)) {
            $conditions[] = 'job_instance_id IN (
                SELECT ji.id
                FROM akeneo_batch_job_instance ji
                WHERE ji.code IN (:job_instance_codes)
            )';
        }

        if ($status !== null) {
            $conditions[] = 'status IN (:status_code)';
        }

        $query = sprintf($query, empty($conditions) ? '' : ' AND ' . implode(' AND ', $conditions));

        return $this->connection->executeQuery(
            $query,
            [
                'create_time' => $timeLimit,
                'status' => BatchStatus::COMPLETED,
                'status_code' => $status?->getValue(),
                'job_instance_codes' => $jobInstanceCodes,
            ],
            [
                'create_time' => Types::DATETIME_MUTABLE,
                'job_instance_codes' => Connection::PARAM_STR_ARRAY
            ]
        );
    }
}
