<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20220901102200__update_rule_execution_job_status_integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20220901102200__update_rule_execution_job_status';
    private const RULE_EXECUTION_JOB_NAME = 'rule_engine_execute_rules';

    private Connection $connection;
    private ?int $jobInstanceId = null;

    /** @test */
    public function it_sets_the_running_rule_execution_job_status_to_failed(): void
    {
        $runningExecutionId = $this->createRuleExecutions(BatchStatus::STARTED, ExitStatus::UNKNOWN);
        $stoppingExecutionId = $this->createRuleExecutions(BatchStatus::STOPPING, ExitStatus::UNKNOWN);
        $startingExecutionId = $this->createRuleExecutions(BatchStatus::STARTING, ExitStatus::UNKNOWN);
        $completeExecutionId = $this->createRuleExecutions(BatchStatus::COMPLETED, ExitStatus::COMPLETED);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->executionFailed($runningExecutionId));
        Assert::assertTrue($this->executionFailed($stoppingExecutionId));
        Assert::assertFalse($this->executionFailed($startingExecutionId));
        Assert::assertFalse($this->executionFailed($completeExecutionId));
    }

    /** @test */
    public function it_does_not_update_the_status_of_executions_with_a_health_check(): void
    {
        $runningExecutionIdWithHealthCheck = $this->createRuleExecutions(
            BatchStatus::STARTED,
            ExitStatus::UNKNOWN,
            true
        );
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertFalse($this->executionFailed($runningExecutionIdWithHealthCheck));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createRuleExecutions(int $status, string $exitCode, bool $withHealthCheckTime = false): int
    {
        $now = new \DateTime('now', new \DateTimezone('UTC'));

        $this->connection->executeStatement(
            <<<SQL
INSERT INTO akeneo_batch_job_execution(job_instance_id, status, start_time, exit_code, health_check_time, raw_parameters)
VALUES (:jobInstanceId, :status, :now, :exitCode, :healthCheckTime, '{}');
SQL,
            [
                'jobInstanceId' => $this->getJobInstanceId(),
                'status' => $status,
                'exitCode' => $exitCode,
                'healthCheckTime' => $withHealthCheckTime ? $now->format('Y-m-d H:i:s') : null,
                'now' => $now->format('Y-m-d H:i:s'),
            ]
        );

        return (int)$this->connection->lastInsertId();
    }

    private function getJobInstanceId(): int
    {
        if (null === $this->jobInstanceId) {
            $res = $this->connection->executeQuery(
                'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
                [
                    'code' => self::RULE_EXECUTION_JOB_NAME,
                ]
            )->fetch();
            Assert::assertNotFalse($res);
            $this->jobInstanceId = (int)$res['id'];
        }

        return $this->jobInstanceId;
    }

    private function executionFailed(int $jobExecutionId): bool
    {
        $res = $this->connection->executeQuery(
            'SELECT status FROM akeneo_batch_job_execution WHERE id = :jobExecutionId',
            [
                'jobExecutionId' => $jobExecutionId,
            ]
        )->fetch();

        return BatchStatus::FAILED == ($res['status'] ?? null);
    }
}
