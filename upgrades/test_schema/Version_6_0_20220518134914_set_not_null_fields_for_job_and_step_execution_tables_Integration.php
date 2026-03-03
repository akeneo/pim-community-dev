<?php

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_6_0_20220518134914_set_not_null_fields_for_job_and_step_execution_tables_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20220518134914_set_not_null_fields_for_job_and_step_execution_tables';

    public function test_does_nothing_when_launched_in_sass_version(): void
    {
        if (!$this->isSassVersion()) {
            $this->markTestSkipped('As version provider cannot be mocked, this test can only be launched on sass version');
        }

        $this->resetTables();

        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'step_count'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_stoppable'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_visible'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_step_execution', 'is_trackable'));

        $this->insertJobExecutionWithNullValues();
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'step_count'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_stoppable'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_visible'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_step_execution', 'is_trackable'));
    }

    public function test_it_update_column_when_launched_in_non_sass_version(): void
    {
        if ($this->isSassVersion()) {
            $this->markTestSkipped('As version provider cannot be mocked, this test can only be launched on non sass version');
        }

        $this->resetTables();

        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'step_count'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_stoppable'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_visible'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_step_execution', 'is_trackable'));

        $this->insertJobExecutionWithNullValues();
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->columnIsNullable('akeneo_batch_job_execution', 'step_count'));
        $this->assertFalse($this->columnIsNullable('akeneo_batch_job_execution', 'is_stoppable'));
        $this->assertFalse($this->columnIsNullable('akeneo_batch_job_execution', 'is_visible'));
        $this->assertFalse($this->columnIsNullable('akeneo_batch_step_execution', 'is_trackable'));
    }

    public function test_migration_is_idempotent(): void
    {
        if ($this->isSassVersion()) {
            $this->markTestSkipped('As version provider cannot be mocked, this test can only be launched on non sass version');
        }

        $this->resetTables();

        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'step_count'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_stoppable'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_job_execution', 'is_visible'));
        $this->assertTrue($this->columnIsNullable('akeneo_batch_step_execution', 'is_trackable'));

        $this->insertJobExecutionWithNullValues();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->columnIsNullable('akeneo_batch_job_execution', 'step_count'));
        $this->assertFalse($this->columnIsNullable('akeneo_batch_job_execution', 'is_stoppable'));
        $this->assertFalse($this->columnIsNullable('akeneo_batch_job_execution', 'is_visible'));
        $this->assertFalse($this->columnIsNullable('akeneo_batch_step_execution', 'is_trackable'));
    }

    private function isSassVersion(): bool
    {
        $versionProvider = static::getContainer()->get('pim_catalog.version_provider');

        return $versionProvider->isSaaSVersion();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertJobExecutionWithNullValues(): void
    {
        $this->getConnection()->executeStatement("
            INSERT INTO akeneo_batch_job_instance (id, code, label, job_name, status, connector, raw_parameters, type)
            VALUES (1, 'compute_product_models_descendants', 'Compute product models descendants', 'compute_product_models_descendants', 0, 'internal', 'a:0:{}', 'compute_product_models_descendants')"
        );

        $this->getConnection()->executeStatement("
            INSERT INTO akeneo_batch_job_execution (id, job_instance_id, create_time, status, raw_parameters, is_stoppable, step_count, is_visible)
            VALUES (1, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}', NULL, NULL, NULL)"
        );

        $this->getConnection()->executeStatement("
            INSERT INTO akeneo_batch_step_execution (job_execution_id, step_name, status, read_count, write_count, filter_count, start_time, end_time, exit_code, exit_description, terminate_only, failure_exceptions, errors, summary, tracking_data, is_trackable)
            VALUES (1, 'validation', 1, 0, 0, 0, '2020-10-16 09:50:28', '2020-10-16 09:50:33', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:\"charset_validator.title\";s:8:\"UTF-8 OK\";}', '{\"totalItems\": 0, \"processedItems\": 0}', NULL)
          ");
    }

    private function columnIsNullable(string $tableName, string $columnName): bool
    {
        $query = <<<SQL
SELECT IS_NULLABLE FROM information_schema.`COLUMNS`
WHERE TABLE_SCHEMA = SCHEMA()
AND TABLE_NAME = :table_name
AND COLUMN_NAME = :column_name;
SQL;

        $isNullable = $this->getConnection()->executeQuery($query, [
            'table_name' => $tableName,
            'column_name' => $columnName,
        ])->fetchOne();

        return $isNullable === "YES";
    }

    private function resetTables(): void
    {
        $this->getConnection()->executeStatement("ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN is_stoppable TINYINT(1) DEFAULT 0 NULL");
        $this->getConnection()->executeStatement("ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN step_count INT DEFAULT 1 NULL");
        $this->getConnection()->executeStatement("ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN is_visible TINYINT(1) DEFAULT 1 NULL");
        $this->getConnection()->executeStatement("ALTER TABLE akeneo_batch_step_execution MODIFY COLUMN is_trackable TINYINT(1) DEFAULT 0 NULL");
    }
}
