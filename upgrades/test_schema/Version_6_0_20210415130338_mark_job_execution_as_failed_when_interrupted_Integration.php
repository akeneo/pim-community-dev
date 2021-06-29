<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_6_0_20210415130338_mark_job_execution_as_failed_when_interrupted_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210415130338_mark_job_execution_as_failed_when_interrupted';
    private const RULE_EXECUTION_JOB_NAME = 'rule_engine_execute_rules';
    private const PROJECT_CALCULATION_JOB_NAME = 'project_calculation';

    private Connection $connection;
    private ?int $jobInstanceId = null;

    /** @test */
    public function it_sets_the_running_rule_execution_job_status_to_failed(): void
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

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->executionFailed($runningExecutionIds));
        Assert::assertTrue($this->executionFailed($stoppingExecutionIds));
        Assert::assertFalse($this->executionFailed($startingExecutionIds));
        Assert::assertFalse($this->executionFailed($completeExecutionIds));
    }

    /** @test */
    public function it_does_not_update_the_status_of_executions_with_a_health_check(): void
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
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertFalse($this->executionFailed($runningExecutionIdsWithHealthCheck));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');

        $this->createJobInstance(self::PROJECT_CALCULATION_JOB_NAME);
        $this->createJobInstance(self::RULE_EXECUTION_JOB_NAME);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
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
}
