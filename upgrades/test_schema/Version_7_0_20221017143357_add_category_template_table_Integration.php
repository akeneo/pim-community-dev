<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_7_0_20221017143357_add_category_template_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221017143357_add_category_template_table';
    private const TABLE_NAME = 'pim_catalog_category_template';

    private const TREE_TEMPLATE_TABLE_NAME = 'pim_catalog_category_tree_template';
    private const FOREIGN_TREE_TEMPLATE_KEY_NAME = 'FK_TREE_TEMPLATE_template_uuid';
    private const ATTRIBUTE_TABLE_NAME = 'pim_catalog_category_attribute';
    private const FOREIGN_ATTRIBUTE_KEY_NAME = 'FK_ATTRIBUTE_template_uuid';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_the_category_template_table_if_not_present(): void
    {
        $this->dropForeignKeyIfExists(self::FOREIGN_TREE_TEMPLATE_KEY_NAME, self::TREE_TEMPLATE_TABLE_NAME);
        $this->dropForeignKeyIfExists(self::FOREIGN_ATTRIBUTE_KEY_NAME, self::ATTRIBUTE_TABLE_NAME);
        Assert::assertTrue($this->tableExists(self::TABLE_NAME));
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_category_template');
        Assert::assertFalse($this->tableExists(self::TABLE_NAME));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists(self::TABLE_NAME));
    }

    /** @test */
    public function it_does_not_fail_if_the_category_template_table_if_already_created(): void
    {
        $this->dropForeignKeyIfExists(self::FOREIGN_TREE_TEMPLATE_KEY_NAME, self::TREE_TEMPLATE_TABLE_NAME);
        $this->dropForeignKeyIfExists(self::FOREIGN_ATTRIBUTE_KEY_NAME, self::ATTRIBUTE_TABLE_NAME);
        Assert::assertTrue($this->tableExists(self::TABLE_NAME));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists(self::TABLE_NAME));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function dropForeignKeyIfExists(string $foreignKeyName, string $tableName): void
    {
        Assert::assertTrue($this->tableExists($tableName));
        if ($this->foreignKeyExists($foreignKeyName, $tableName)) {
            $this->connection->executeQuery(
                sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $tableName, $foreignKeyName)
            );
        }

        Assert::assertEquals(false, $this->foreignKeyExists($foreignKeyName, $tableName));
    }

    private function tableExists(string $tableName): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                [
                    'tableName' => $tableName,
                ]
            )->rowCount() >= 1;
    }

    private function foreignKeyExists(string $foreignKeyName, string $tableName): bool
    {
        $foreignKeys = $this->connection->getSchemaManager()->listTableForeignKeys($tableName);
        $foreignKeyFound = array_filter($foreignKeys, function ($foreignKey) use ($foreignKeyName) {
            return ($foreignKey->getName() === $foreignKeyName);
        });
        return count($foreignKeyFound) > 0;
    }
}
