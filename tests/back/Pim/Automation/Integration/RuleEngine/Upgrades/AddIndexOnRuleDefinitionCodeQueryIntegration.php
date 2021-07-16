<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Upgrades;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Upgrades\AddIndexOnRuleDefinitionCodeQuery;
use Doctrine\DBAL\Connection;

class AddIndexOnRuleDefinitionCodeQueryIntegration extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var AddIndexOnRuleDefinitionCodeQuery */
    private $addIndexQuery;

    public function test_it_adds_index_on_rule_definition_table()
    {
        $this->dropIndex();

        $this->addIndexQuery->execute();
        $this->assertIndexInTable(true);
    }

    public function test_it_adds_index_once()
    {
        $this->assertIndexInTable(true);

        $this->addIndexQuery->execute();
        $this->assertIndexInTable(true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->addIndexQuery = $this->get(AddIndexOnRuleDefinitionCodeQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function dropIndex(): void
    {
        $sql = <<<SQL
DROP INDEX akeneo_rule_engine_rule_definition_code__index ON akeneo_rule_engine_rule_definition;
SQL;
        $this->connection->executeQuery($sql);
        $this->assertIndexInTable(false);
    }

    private function assertIndexInTable($expected)
    {
        $countIndexesSQL = <<<SQL
SELECT COUNT(*) CountIndexes FROM INFORMATION_SCHEMA.STATISTICS
WHERE table_schema=DATABASE() AND
      table_name='akeneo_rule_engine_rule_definition';
SQL;
        $countIndexes = $this->connection->executeQuery($countIndexesSQL)->fetchColumn();
        // Primary key count as an index that's why there is always at least one index.
        $this->assertEquals($expected ? '2' : '1', $countIndexes, 'Expected count is wrong');

        $isIndexHereSQL = <<<SQL
SELECT COUNT(1) IsIndexHere FROM INFORMATION_SCHEMA.STATISTICS
WHERE table_schema=DATABASE() AND
      table_name='akeneo_rule_engine_rule_definition' AND
      index_name='akeneo_rule_engine_rule_definition_code__index';
SQL;
        $isIndexHere = $this->connection->executeQuery($isIndexHereSQL)->fetchColumn();
        $this->assertEquals($expected ? '1' : '0', $isIndexHere, 'Expected index is wrong');
    }
}
