<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @todo @pull-up Do not pull-up this test and its migration script in master/6.0 (cf PIM-10179)
 */
final class Version_5_0_20211125143931_migrate_longtext_to_json_for_job_execution_queue_Integration  extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_5_0_20211125143931_migrate_longtext_to_json_for_job_execution_queue';

    public function test_it_migrates_longtext_to_json_for_execution_job_queue(): void
    {
        $sql = <<<SQL
        ALTER TABLE akeneo_batch_job_execution_queue 
        MODIFY COLUMN options longtext;
        SQL;

        $this->get('database_connection')->executeQuery($sql);

        Assert::assertSame('longtext', $this->getColumnType('akeneo_batch_job_execution_queue', 'options'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertSame('json', $this->getColumnType('akeneo_batch_job_execution_queue', 'options'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getColumnType(string $tableName, string $columnName): string
    {
        $dbConnection = $this->get('database_connection');

        $sql = <<<SQL
        SELECT DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = :db_name AND table_name = :table_name AND column_name = :column_name
        SQL;

        $statement = $dbConnection->executeQuery($sql, [
            'db_name' => $dbConnection->getParams()['dbname'],
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }
}
