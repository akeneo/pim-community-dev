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
class Version_6_0_20211124154203_add_is_visible_in_job_execution_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211124154203_add_is_visible_in_job_execution';

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

    public function test_it_adds_a_is_visible_column_to_the_job_execution_table(): void
    {
        $this->dropColumnIfExists();

        $nonVisibleJobInstanceId = $this->getJobInstanceId('compute_family_variant_structure_changes');
        $nonVisibleJobExecutionId = $this->createJobExecution($nonVisibleJobInstanceId);

        $visibleJobInstanceId = $this->getJobInstanceId('csv_product_quick_export');
        $visibleJobExecutionId = $this->createJobExecution($visibleJobInstanceId);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->columnExists());

        $jobExecutions = $this->selectJobExecutions();

        Assert::assertEquals([
            $nonVisibleJobExecutionId => '0',
            $visibleJobExecutionId => '1',
        ], $jobExecutions);
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropColumnIfExists();

        $jobInstanceId = $this->getJobInstanceId('csv_product_quick_export');
        $this->createJobExecution($jobInstanceId);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->columnExists());
    }

    private function dropColumnIfExists(): void
    {
        if ($this->columnExists()) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP COLUMN is_visible;');
        }

        Assert::assertEquals(false, $this->columnExists());
    }

    private function columnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('akeneo_batch_job_execution');

        return isset($columns['is_visible']);
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
            ],
        );

        return (int) $this->connection->lastInsertId();
    }

    private function getJobInstanceId(string $code): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $code],
        )->fetchColumn();
    }

    private function selectJobExecutions(): array
    {
        $result = $this->connection->executeQuery('SELECT id, is_visible FROM akeneo_batch_job_execution')->fetchAllAssociative();

        return array_column($result, 'is_visible', 'id');
    }
}
