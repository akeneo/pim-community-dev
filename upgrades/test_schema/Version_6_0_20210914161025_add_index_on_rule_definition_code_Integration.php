<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210914161025_add_index_on_rule_definition_code_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210914161025_add_index_on_rule_definition_code';

    public function test_it_adds_index_on_rule_definition_table()
    {
        $this->dropIndex();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertIndexInTable(true);
    }

    public function test_it_adds_index_once()
    {
        $this->assertIndexInTable(true);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertIndexInTable(true);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertIndexInTable(bool $expected): void
    {
        $countIndexesSQL = <<<SQL
            SELECT COUNT(*) indexCount 
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE table_schema=DATABASE()
            AND table_name='akeneo_rule_engine_rule_definition';
SQL;

        $countIndexes = $this->getConnection()->executeQuery($countIndexesSQL)->fetchOne();
        // Primary key count as an index that's why there is always at least one index.
        $this->assertEquals($expected ? '2' : '1', $countIndexes, 'Expected count is wrong');

        $isIndexHereSQL = <<<SQL
            SELECT COUNT(1) IsIndexHere 
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE table_schema = DATABASE()
            AND table_name = 'akeneo_rule_engine_rule_definition'
            AND index_name = 'akeneo_rule_engine_rule_definition_code__index';
        SQL;

        $isIndexHere = $this->getConnection()->executeQuery($isIndexHereSQL)->fetchOne();
        $this->assertEquals($expected ? '1' : '0', $isIndexHere, 'Expected index is wrong');
    }

    private function dropIndex(): void
    {
        $sql = <<<SQL
            DROP INDEX akeneo_rule_engine_rule_definition_code__index ON akeneo_rule_engine_rule_definition;
        SQL;

        $this->getConnection()->executeQuery($sql);
        $this->assertIndexInTable(false);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
