<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_8_0_20230818093227_add_is_visible_on_batch_job_instance_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230818093227_add_is_visible_on_batch_job_instance_table';
    private const TABLE_NAME = 'akeneo_batch_job_instance';
    private const IS_VISIBLE_COLUMN = 'is_visible';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function test_it_adds_a_is_visible_column_to_the_job_instance_table(): void
    {
        $this->dropColumnIfExists();
        Assert::assertFalse($this->columnExists());

        $visibleJobInstanceId = $this->createJobInstance('xlsx_family_import');
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->columnExists());

        $nonVisibleJobInstanceId = $this->createJobInstance('csv_user_group_export', false);

        $jobInstances = $this->selectJobInstances([$visibleJobInstanceId, $nonVisibleJobInstanceId]);

        Assert::assertEqualsCanonicalizing([
            $visibleJobInstanceId => '1',
            $nonVisibleJobInstanceId => '0',
        ], $jobInstances);
    }

    /**
     * @test
     */
    public function test_migration_is_idempotent(): void
    {
        $this->dropColumnIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->columnExists());
    }

    private function dropColumnIfExists(): void
    {
        if ($this->columnExists()) {
            $this->connection->executeQuery(
                sprintf('ALTER TABLE %s DROP COLUMN %s;', self::TABLE_NAME, self::IS_VISIBLE_COLUMN)
            );
        }
    }

    private function columnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns(self::TABLE_NAME);

        return isset($columns[self::IS_VISIBLE_COLUMN]);
    }

    private function createJobInstance(string $label, bool $isVisible = true): int
    {
        $parameters = [
            'label' => $label,
            'code' => $label,
            'job_name' => $label,
            'status' => 0,
            'connector' => 'Akeneo CSV Connector',
            'raw_parameters' => serialize([]),
            'type' => 'export',
        ];

        if (!$isVisible) {
            $parameters[self::IS_VISIBLE_COLUMN] = 0;
        }

        $this->connection->insert(self::TABLE_NAME, $parameters);

        return (int) $this->connection->lastInsertId();
    }

    private function selectJobInstances(array $jobInstanceIds): array
    {
        $result = $this->connection->executeQuery(
            sprintf('SELECT id, %s FROM %s WHERE id IN (:ids)', self::IS_VISIBLE_COLUMN, self::TABLE_NAME),
            ['ids' => $jobInstanceIds],
            ['ids' => Connection::PARAM_STR_ARRAY],
        )->fetchAllAssociative();

        return array_column($result, self::IS_VISIBLE_COLUMN, 'id');
    }
}
