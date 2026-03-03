<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlMarkJobExecutionAsFailedWhenInterrupted;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class SqlMarkJobExecutionAsFailedWhenInterruptedIntegration extends TestCase
{
    private Connection $connection;
    private SqlMarkJobExecutionAsFailedWhenInterrupted $markJobAsFailed;

    public function test_it_sets_the_running_rule_execution_job_status_to_failed(): void
    {
        $runningExecutionIds = [
            $this->createJobExecutions('whatever_job', BatchStatus::STARTED, ExitStatus::UNKNOWN),
            $this->createJobExecutions('another_job', BatchStatus::STARTED, ExitStatus::UNKNOWN),
        ];
        $stoppingExecutionIds = [
            $this->createJobExecutions('whatever_job', BatchStatus::STOPPING, ExitStatus::UNKNOWN),
            $this->createJobExecutions('another_job', BatchStatus::STOPPING, ExitStatus::UNKNOWN),
        ];
        $startingExecutionIds = [
            $this->createJobExecutions('whatever_job', BatchStatus::STARTING, ExitStatus::UNKNOWN),
            $this->createJobExecutions('another_job', BatchStatus::STARTING, ExitStatus::UNKNOWN),
        ];
        $completeExecutionIds = [
            $this->createJobExecutions('whatever_job', BatchStatus::COMPLETED, ExitStatus::COMPLETED),
            $this->createJobExecutions('another_job', BatchStatus::COMPLETED, ExitStatus::COMPLETED),
        ];

        $this->markJobAsFailed->execute(['another_job', 'whatever_job']);

        Assert::assertTrue($this->executionFailed($runningExecutionIds));
        Assert::assertTrue($this->executionFailed($stoppingExecutionIds));
        Assert::assertFalse($this->executionFailed($startingExecutionIds));
        Assert::assertFalse($this->executionFailed($completeExecutionIds));
    }

    public function test_it_does_not_update_the_status_of_executions_with_a_health_check(): void
    {
        $runningExecutionIdsWithHealthCheck = [];
        $runningExecutionIdsWithHealthCheck[] = $this->createJobExecutions(
            'whatever_job',
            BatchStatus::STARTED,
            ExitStatus::UNKNOWN,
            true
        );
        $runningExecutionIdsWithHealthCheck[] = $this->createJobExecutions(
            'another_job',
            BatchStatus::STARTED,
            ExitStatus::UNKNOWN,
            true
        );
        $this->markJobAsFailed->execute(['another_job', 'whatever_job']);

        Assert::assertFalse($this->executionFailed($runningExecutionIdsWithHealthCheck));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->markJobAsFailed = $this->get(SqlMarkJobExecutionAsFailedWhenInterrupted::class);

        $this->createJobInstance('whatever_job');
        $this->createJobInstance('another_job');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createJobExecutions(string $jobCode, int $status, string $exitCode, bool $withHealthCheckTime = false): int
    {
        $now = new \DateTime('now', new \DateTimezone('UTC'));

        $this->connection->executeUpdate(
            <<<SQL
INSERT INTO akeneo_batch_job_execution (job_instance_id, status, start_time, exit_code, health_check_time, raw_parameters)
    SELECT job.id, :status, :now, :exitCode, :healthCheckTime, '{}' 
    FROM akeneo_batch_job_instance job
    WHERE job.code = :jobCode;
SQL,
            [
                'jobCode' => $jobCode,
                'status' => $status,
                'exitCode' => $exitCode,
                'healthCheckTime' => $withHealthCheckTime ? $now->format('Y-m-d H:i:s') : null,
                'now' => $now->format('Y-m-d H:i:s'),
            ]
        );

        return (int) $this->connection->lastInsertId();
    }

    private function executionFailed(array $jobExecutionIds): bool
    {
        $results = $this->connection->executeQuery(
            'SELECT status FROM akeneo_batch_job_execution WHERE id IN (:jobExecutionIds)',
            [
                'jobExecutionIds' => $jobExecutionIds,
            ],
            [
                'jobExecutionIds' => Connection::PARAM_INT_ARRAY,
            ]
        )->fetchAll();

        $isFailed = false;
        foreach ($results as $result) {
            if (BatchStatus::FAILED == ($result['status'] ?? null)) {
                $isFailed = true;
            }
        }

        return $isFailed;
    }

    private function createJobInstance(string $jobCode): void
    {
        $this->connection->executeQuery(
            <<<SQL
INSERT IGNORE INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type) 
VALUES (:code, :code, :code, 0, 'internal', 'a:0:{}', :code)
SQL,
            ['code' => $jobCode]
        );
    }
}
