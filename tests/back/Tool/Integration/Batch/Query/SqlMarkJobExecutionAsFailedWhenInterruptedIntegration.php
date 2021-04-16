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

class SqlCleanRuleExecutionJobStatusIntegration extends TestCase
{
    private const RULE_EXECUTION_JOB_NAME = 'rule_engine_execute_rules';
    private const PROJECT_CALCULATION_JOB_NAME = 'project_calculation';

    private Connection $connection;
    private SqlMarkJobExecutionAsFailedWhenInterrupted $markJobAsFailed;
    private ?int $jobInstanceId = null;

    public function test_it_sets_the_running_rule_execution_job_status_to_failed(): void
    {
        $runningExecutionIds = [
            $this->createJobExecutions(self::RULE_EXECUTION_JOB_NAME,BatchStatus::STARTED, ExitStatus::UNKNOWN),
            $this->createJobExecutions(self::PROJECT_CALCULATION_JOB_NAME,BatchStatus::STARTED, ExitStatus::UNKNOWN),
        ];
        $stoppingExecutionIds = [
            $this->createJobExecutions(self::RULE_EXECUTION_JOB_NAME,BatchStatus::STOPPING, ExitStatus::UNKNOWN),
            $this->createJobExecutions(self::PROJECT_CALCULATION_JOB_NAME,BatchStatus::STOPPING, ExitStatus::UNKNOWN),
        ];
        $startingExecutionIds = [
            $this->createJobExecutions(self::RULE_EXECUTION_JOB_NAME,BatchStatus::STARTING, ExitStatus::UNKNOWN),
            $this->createJobExecutions(self::PROJECT_CALCULATION_JOB_NAME,BatchStatus::STARTING, ExitStatus::UNKNOWN),
        ];
        $completeExecutionIds = [
            $this->createJobExecutions(self::RULE_EXECUTION_JOB_NAME,BatchStatus::COMPLETED, ExitStatus::COMPLETED),
            $this->createJobExecutions(self::PROJECT_CALCULATION_JOB_NAME,BatchStatus::COMPLETED, ExitStatus::COMPLETED),
        ];

        $this->markJobAsFailed->execute([self::PROJECT_CALCULATION_JOB_NAME, self::RULE_EXECUTION_JOB_NAME]);

        Assert::assertTrue($this->executionFailed($runningExecutionIds));
        Assert::assertTrue($this->executionFailed($stoppingExecutionIds));
        Assert::assertFalse($this->executionFailed($startingExecutionIds));
        Assert::assertFalse($this->executionFailed($completeExecutionIds));
        }

    public function test_it_does_not_update_the_status_of_executions_with_a_health_check(): void
    {
        $runningExecutionIdsWithHealthCheck = [];
        $runningExecutionIdsWithHealthCheck[] = $this->createJobExecutions(
            self::RULE_EXECUTION_JOB_NAME,
            BatchStatus::STARTED,
            ExitStatus::UNKNOWN,
            true
        );
        $runningExecutionIdsWithHealthCheck[] = $this->createJobExecutions(
            self::PROJECT_CALCULATION_JOB_NAME,
            BatchStatus::STARTED,
            ExitStatus::UNKNOWN,
            true
        );
        $this->markJobAsFailed->execute([self::PROJECT_CALCULATION_JOB_NAME, self::RULE_EXECUTION_JOB_NAME]);

        Assert::assertFalse($this->executionFailed($runningExecutionIdsWithHealthCheck));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->markJobAsFailed = $this->get(SqlMarkJobExecutionAsFailedWhenInterrupted::class);
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
INSERT INTO akeneo_batch_job_execution(job_instance_id, status, start_time, exit_code, health_check_time, raw_parameters)
VALUES (:jobInstanceId, :status, :now, :exitCode, :healthCheckTime, '{}');
SQL,
            [
                'jobInstanceId' => $this->getJobInstanceId($jobCode),
                'status' => $status,
                'exitCode' => $exitCode,
                'healthCheckTime' => $withHealthCheckTime ? $now->format('Y-m-d H:i:s') : null,
                'now' => $now->format('Y-m-d H:i:s'),
            ]
        );

        return (int)$this->connection->lastInsertId();
    }

    private function getJobInstanceId(string $jobCode): int
    {
        if (null === $this->jobInstanceId) {
            $res = $this->connection->executeQuery(
                'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
                [
                    'code' => $jobCode,
                ]
            )->fetch();
            Assert::assertNotFalse($res);
            $this->jobInstanceId = (int) $res['id'];
        }

        return $this->jobInstanceId;
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
}
