<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20210101000000_fix_json_columns_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210101000000_fix_json_columns';

    private const AFFECTED_COLUMNS = [
        ['akeneo_batch_job_execution', 'raw_parameters'],
        ['oro_user', 'product_grid_filters'],
        ['oro_user', 'properties'],
    ];

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_fixes_json_columns()
    {
        $initial = $this->getAffectedColumnDefinitions();

        // artificial corruption of the default DB
        $corrupted = [];
        foreach (self::AFFECTED_COLUMNS as $index => $col) {
            $this->corruptJsonColumn($initial[$index], ...$col);
            $corrupted[$index] = $this->getColumnDefinition(...$col);
            $this->assertOnlyCommentChanged($initial[$index], $corrupted[$index], '(DC2Type:json_array)', ...$col);
        }

        // after applying the migration, everything should be back to normal
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertEquals($initial, $this->getAffectedColumnDefinitions());
    }

    /**
     * Creates a json column comment so we can fix it later
     * This avoids managing a "corrupted" DB dump just for this test
     */
    private function corruptJsonColumn(array $definition, string $table, string $column)
    {
        $isNullable = ('YES' === $definition['IS_NULLABLE']);

        $sql = <<<SQL
        ALTER TABLE %s
        MODIFY %s json %s COMMENT "(DC2Type:json_array)"
SQL;

        $this->getConnection()->executeStatement(sprintf(
            $sql,
            $table,
            $column,
            $isNullable ? 'DEFAULT NULL' : 'NOT NULL',
        ));
    }

    /**
     * Make sure that we don't modify more than expected (ie: the comment)
     */
    private function assertOnlyCommentChanged(array $before, array $after, string $expected, string $table, string $column)
    {
        $diff = array_diff_assoc($after, $before);

        $data = print_r([
            'table' => $table,
            'column' => $column,
            'before' => $before,
            'after' => $after,
            'diff' => $diff,
        ], true);

        $this->assertCount(1, $diff, $data);
        $this->assertEquals('COLUMN_COMMENT', array_keys($diff)[0], $data);
        $this->assertEquals($expected, $after['COLUMN_COMMENT'], $data);
    }

    private function getAffectedColumnDefinitions(): array
    {
        return array_map(fn ($col) => $this->getColumnDefinition(...$col), self::AFFECTED_COLUMNS);
    }

    private function getColumnDefinition(string $table, string $column): array
    {
        $sql = <<<SQL
        SELECT * FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE()
        AND TABLE_NAME=:table_name
        AND COLUMN_NAME=:column_name;
SQL;
        return $this->getConnection()
            ->executeQuery($sql, [
                'table_name' => $table,
                'column_name' => $column,
            ])
            ->fetchAssociative();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
