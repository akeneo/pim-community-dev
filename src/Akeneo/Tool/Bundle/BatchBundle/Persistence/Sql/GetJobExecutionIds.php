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

    public function olderThanDays(int $days): Result
    {
        if ($days < 1) {
            throw new InvalidArgumentException(
                sprintf('Number of days should be strictly superior to 0, "%s given', $days)
            );
        }

        $endTime = new DateTime();
        $endTime->modify(sprintf('- %d days', $days));

        return $this->fetchOlderThanTime($endTime);
    }

    public function olderThanHours(int $hours): Result
    {
        Assert::greaterThanEq(
            $hours,
            1,
            sprintf('Number of hours should be at least 1, "%s given', $hours)
        );

        $endTime = new DateTime();
        $endTime->modify(sprintf('- %d hours', $hours));

        return $this->fetchOlderThanTime($endTime);
    }

    public function all(): Result
    {
        $query = <<<SQL
            SELECT id
            FROM akeneo_batch_job_execution
        SQL;

        return $this->connection->executeQuery($query);
    }

    private function fetchOlderThanTime(DateTime $timeLimit): Result
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
        SQL;

        return $this->connection->executeQuery(
            $query,
            ['create_time' => $timeLimit, 'status' => BatchStatus::COMPLETED],
            ['create_time' => Types::DATETIME_MUTABLE]
        );
    }
}
