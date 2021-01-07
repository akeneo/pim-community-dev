<?php
declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_6_0_20210106090000_dqi_alter_dictionary_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210106090000_dqi_alter_dictionary_table';

    public function test_it_adds_a_primary_key()
    {
        $dropColumnSql = 'ALTER TABLE pimee_data_quality_insights_text_checker_dictionary DROP COLUMN id;';
        $this->get('database_connection')->executeQuery($dropColumnSql);
        $this->assertFalse(
            $this->isColumnAPrimaryKey('pimee_data_quality_insights_text_checker_dictionary', 'id'),
            'The "id" column of the pimee_data_quality_insights_text_checker_dictionary table was found as a primary key.'
        );

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue(
            $this->isColumnAPrimaryKey('pimee_data_quality_insights_text_checker_dictionary', 'id'),
            'The "id" column of the pimee_data_quality_insights_text_checker_dictionary table was not found as a primary key.'
        );
    }

    protected function isColumnAPrimaryKey(string $table, string $column): bool
    {
        return 1 === $this->get('database_connection')
                ->executeQuery(sprintf('SHOW KEYS FROM %s WHERE Key_name = "PRIMARY" and Column_name = "%s";', $table, $column))
                ->rowCount();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
