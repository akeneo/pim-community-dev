<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use PHPUnit\Framework\Assert;

final class Version_7_0_20221019174400_add_template_uuid_column_on_category_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221019174400_add_template_uuid_column_on_category';
    private const TABLE_NAME = 'pim_catalog_category';
    private const COLUMN_NAME = 'category_template_uuid';
    private const FOREIGN_KEY_NAME = 'FK_CATEGORY_template_uuid';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function test_it_adds_category_template_uuid_to_the_pim_catalog_category_table(): void
    {
        $this->dropForeignKeyIfExists();
        $this->dropColumnIfExists();
        Assert::assertEquals(false, $this->columnExists());
        Assert::assertEquals(false, $this->foreignKeyExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->columnExists());
        Assert::assertEquals(true, $this->foreignKeyExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function dropColumnIfExists(): void
    {
        if ($this->columnExists()) {
            $this->connection->executeQuery(
                sprintf('ALTER TABLE %s DROP COLUMN %s', self::TABLE_NAME, self::COLUMN_NAME)
            );
        }

        Assert::assertEquals(false, $this->columnExists());
    }

    private function columnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns(self::TABLE_NAME);

        return isset($columns[self::COLUMN_NAME]);
    }

    private function dropForeignKeyIfExists(): void
    {
        if ($this->foreignKeyExists()) {
            $this->connection->executeQuery(
                sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', self::TABLE_NAME, self::FOREIGN_KEY_NAME)
            );
        }

        Assert::assertEquals(false, $this->foreignKeyExists());
    }

    private function foreignKeyExists(): bool
    {
        $foreignKeys = $this->connection->getSchemaManager()->listTableForeignKeys(self::TABLE_NAME);
        $foreignKeyFound = array_filter($foreignKeys, function ($foreignKey) {
            return ($foreignKey->getName() === self::FOREIGN_KEY_NAME);
        });
        return count($foreignKeyFound) > 0;
    }
}
