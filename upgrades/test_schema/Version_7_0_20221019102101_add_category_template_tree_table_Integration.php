<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_7_0_20221019102101_add_category_template_tree_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221019102101_add_category_template_tree_table';
    private const TABLE_NAME = 'pim_catalog_category_tree_template';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_the_category_tree_template_table_if_not_present(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_category_tree_template');
        Assert::assertFalse($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    /** @test */
    public function it_does_not_fail_if_the_category_tree_template_table_if_already_created(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function tableExists(): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                [
                    'tableName' => self::TABLE_NAME,
                ]
            )->rowCount() >= 1;
    }
}
