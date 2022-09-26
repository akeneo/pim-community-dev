<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteJobExecution
{
    public function __construct(private Connection $connection)
    {
    }

    public function olderThanDays(int $days): int
    {
        if ($days < 1) {
            throw new \InvalidArgumentException(
                sprintf('Number of days should be strictly superior to 0, "%s% given', $days)
            );
        }

        $endTime = new \DateTime();
        $endTime->modify(sprintf('- %d days', $days));

        return $this->deleteOlderThanTime($endTime);
    }

    public function olderThanHours(int $hours): int
    {
        Assert::greaterThanEq(
            $hours,
            1,
            sprintf('Number of hours should be at least 1, "%s given', $hours)
        );

        $endTime = new \DateTime();
        $endTime->modify(sprintf('- %d hours', $hours));

        return $this->deleteOlderThanTime($endTime);
    }

    public function all(): int
    {
        $query = 'DELETE FROM akeneo_batch_job_execution';

        return $this->connection->executeStatement($query);
    }

    /**
     * The two subqueries seem useless but it's a trick.
     * It is to avoid a limitation in Mysql that prevents the deletion in the same table as the subquery.
     * The query cannot be executed without it.
     */
    public function deleteOlderThanTime(\DateTime $createdTimeLimit): int
    {
        $query = <<<SQL
            DELETE FROM akeneo_batch_job_execution WHERE id IN (
                SELECT id FROM (
                    SELECT id 
                    FROM akeneo_batch_job_execution
                    WHERE akeneo_batch_job_execution.create_time < :create_time AND akeneo_batch_job_execution.id NOT IN (
                        SELECT MAX(last_job_execution.id) 
                        FROM akeneo_batch_job_execution last_job_execution 
                        WHERE last_job_execution.status = :status 
                        GROUP BY last_job_execution.job_instance_id
                    )
                ) as job_execution_to_remove
            )
        SQL;

        return $this->connection->executeStatement(
            $query,
            ['create_time' => $createdTimeLimit, 'status' => BatchStatus::COMPLETED],
            ['create_time' => Types::DATETIME_MUTABLE]
        );
    }
}
