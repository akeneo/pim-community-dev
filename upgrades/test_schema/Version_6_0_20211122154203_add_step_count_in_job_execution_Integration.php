<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211122154203_add_step_count_in_job_execution_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211122154203_add_step_count_in_job_execution';

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_step_count_column_to_the_job_execution_table(): void
    {
        $this->dropColumnIfExists();

        $stoppableJobInstanceId = $this->createJobInstance('csv_user_group_export');
        $this->createJobExecution($stoppableJobInstanceId);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->columnExists());

        Assert::assertNull($this->selectStepCount());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropColumnIfExists();

        $jobInstanceId = $this->createJobInstance('an_export');
        $this->createJobExecution($jobInstanceId);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->columnExists());
    }

    private function dropColumnIfExists(): void
    {
        if ($this->columnExists()) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP COLUMN step_count;');
        }

        Assert::assertEquals(false, $this->columnExists());
    }

    private function columnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('akeneo_batch_job_execution');

        return isset($columns['step_count']);
    }

    private function createJobExecution(int $jobInstanceId): int
    {
        $this->connection->insert(
            'akeneo_batch_job_execution',
            [
                'job_instance_id' => $jobInstanceId,
                'status' => 1,
                'raw_parameters' => [],
            ],
            [
                'raw_parameters' => Types::JSON,
            ]
        );

        return (int)$this->connection->lastInsertId();
    }

    private function createJobInstance(string $label): int
    {
        $this->connection->insert(
            'akeneo_batch_job_instance',
            [
                'label' => $label,
                'code' => $label,
                'job_name' => $label,
                'status' => 0,
                'connector' => 'Akeneo CSV Connector',
                'raw_parameters' => serialize([]),
                'type' => 'export',
            ],
        );

        return (int) $this->connection->lastInsertId();
    }

    private function selectStepCount(): ?int
    {
        return $this->connection->executeQuery('SELECT step_count FROM akeneo_batch_job_execution')->fetchOne()['count'] ?? null;
    }
}
