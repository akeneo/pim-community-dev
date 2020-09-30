<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200915083948_add_enabled_column_on_rule_definition_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200915083948_add_enabled_column_on_rule_definition';

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_adds_the_enabled_column_with_default_true(): void
    {
        $dropColumnSql = 'ALTER TABLE akeneo_rule_engine_rule_definition DROP COLUMN enabled;';
        $this->get('database_connection')->executeQuery($dropColumnSql);
        $this->assertFalse(
            $this->isColumnInSchema('akeneo_rule_engine_rule_definition', 'enabled'),
            'The "enabled" column of the akeneo_rule_engine_rule_definition table is still in the schema.'
        );
        $this->createRules();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue(
            $this->isColumnInSchema('akeneo_rule_engine_rule_definition', 'enabled'),
            'The "enabled" column of the akeneo_rule_engine_rule_definition table was not found.'
        );
        $this->assertRulesAreEnabledAfterMigration();
    }

    protected function isColumnInSchema(string $table, string $column): bool
    {
        return 1 === $this->get('database_connection')
            ->executeQuery(sprintf('SHOW COLUMNS FROM %s LIKE "%s";', $table, $column))
            ->rowCount();
    }

    private function createRules(): void
    {
        $addRulesSql = <<<SQL
INSERT INTO akeneo_rule_engine_rule_definition (code, type, content, priority, impacted_subject_count) VALUES
    ('test1', 'product', '{"actions": [{"type": "set", "field": "camera_brand", "value": "canon_brand"}], "conditions": [{"field": "family", "value": ["camcorders"], "operator": "IN"}, {"field": "name", "value": "Canon", "operator": "CONTAINS"}, {"field": "camera_brand", "value": ["canon_brand"], "operator": "NOT IN"}]}', 0, null),
    ('test2', 'product', '{"actions": [{"type": "copy", "to_field": "camera_model_name", "from_field": "name"}], "conditions": [{"field": "family", "value": ["camcorders"], "operator": "IN"}, {"field": "camera_model_name", "operator": "EMPTY"}]}', 0, null)
;
SQL;
        $this->get('database_connection')->executeQuery($addRulesSql);
    }

    private function assertRulesAreEnabledAfterMigration(): void
    {
        $stmt = $this->get('database_connection')->executeQuery(
            'SELECT enabled FROM akeneo_rule_engine_rule_definition'
        );
        $enableds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->assertNotEmpty($enableds);
        foreach ($enableds as $enabled) {
            $this->assertSame('1', $enabled);
        }
    }
}
