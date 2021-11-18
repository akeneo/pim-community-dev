<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20211117163832_add_required_for_completeness_column_Integration extends TestCase
{
    private const MIGRATION_LABEL = '_6_0_20211117163832_add_required_for_completeness_column';

    use ExecuteMigrationTrait;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    public function test_it_adds_a_new_required_for_completeness_column_to_the_catalog_table_column_table(): void
    {
        $connection = $this->getConnection();

        $connection->executeQuery('INSERT INTO pim_catalog_table_column(id, attribute_id, code, data_type, column_order, is_required_for_completeness) SELECT "1", id, "column1", "select", 0, 0 FROM pim_catalog_attribute');
        $connection->executeQuery('INSERT INTO pim_catalog_table_column(id, attribute_id, code, data_type, column_order, is_required_for_completeness) SELECT "2", id, "column2", "select", 1, 0 FROM pim_catalog_attribute');
        $connection->executeQuery('INSERT INTO pim_catalog_table_column(id, attribute_id, code, data_type, column_order, is_required_for_completeness) SELECT "3", id, "column3", "select", 2, 1 FROM pim_catalog_attribute');

        $connection->executeQuery('ALTER TABLE pim_catalog_table_column DROP COLUMN is_required_for_completeness;');
        Assert::assertFalse($this->updatedColumnExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->updatedColumnExists());

        $row = $connection->executeQuery('SELECT * from pim_catalog_table_column WHERE column_order = 0')->fetchAssociative();
        Assert::assertEquals(1, $row['is_required_for_completeness']);

        $row = $connection->executeQuery('SELECT * from pim_catalog_table_column WHERE column_order = 2')->fetchAssociative();
        Assert::assertEquals(0, $row['is_required_for_completeness']);
    }

    private function updatedColumnExists(): bool
    {
        $columns = $this->getConnection()->getSchemaManager()->listTableColumns('pim_catalog_table_column');

        return isset($columns['is_required_for_completeness']);
    }
}
