<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_7_0_20221130130031_update_identifier_generator_prefix_number_type_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;
    private const MIGRATION_LABEL = '_7_0_20221130130031_update_identifier_generator_prefix_number_type';

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_changes_column_type(): void
    {
        $this->rollback();
        Assert::assertEquals($this->getColumnType(), 'int');
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals($this->getColumnType(), 'bigint unsigned');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function rollback(): void
    {
        $this->connection->executeQuery(<<<SQL
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    MODIFY `number` INT NOT NULL;
SQL);
    }

    private function getColumnType(): string
    {
        $sql = <<<SQL
SELECT COLUMN_TYPE from information_schema.COLUMNS 
WHERE TABLE_SCHEMA='%s' 
  AND TABLE_NAME='pim_catalog_identifier_generator_prefixes'
  AND COLUMN_NAME='number';
SQL;

        return $this->connection->fetchOne(\sprintf($sql, $this->connection->getDatabase()));
    }
}
