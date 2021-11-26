<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\FetchMode;
use PHPUnit\Framework\Assert;

/**
 * @todo @pull-up Do not pull-up this test and its migration script in master/6.0 (cf PIM-10179)
 */
final class Version_5_0_20211125112429_remove_datagrid_view_unique_label_Integration  extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_5_0_20211125112429_remove_datagrid_view_unique_label';

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_removes_the_constraint_if_it_exists()
    {
        if (!$this->constraintExists()) {
            $this->createUniqueIndex();
        }

        Assert::assertTrue($this->constraintExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertFalse($this->constraintExists());
    }

    private function constraintExists(): bool
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->get('database_connection')->executeQuery($databaseNameSql)->fetch(FetchMode::COLUMN);
        Assert::assertIsString($databaseName);

        $findConstraintNameSql = <<< SQL
        SELECT DISTINCT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE table_name = 'pim_datagrid_view' AND constraint_type = 'UNIQUE' AND TABLE_SCHEMA = :database_name;
        SQL;

        $uniqueConstraintName = $this->get('database_connection')->executeQuery($findConstraintNameSql, [
            'database_name' => $databaseName,
        ])->fetch(FetchMode::COLUMN);

        return is_string($uniqueConstraintName);
    }

    private function createUniqueIndex()
    {
        $this->get('database_connection')->executeQuery(
            'CREATE UNIQUE INDEX pim_datagrid_view_label_unique ON pim_datagrid_view (label)'
        );
    }
}
