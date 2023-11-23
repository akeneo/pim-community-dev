<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20220524145600_add_updated_index_on_category_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20220524145600_add_updated_index_on_category';

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_new_index_on_category_table(): void
    {
        $this->dropIndexIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->indexExists('updated_idx'));
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropIndexIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        Assert::assertTrue($this->indexExists());
    }

    private function dropIndexIfExists(): void
    {
        if ($this->indexExists()) {
            $this->connection->executeQuery('ALTER TABLE pim_catalog_category DROP INDEX updated_idx;');
        }

        Assert::assertEquals(false, $this->indexExists());
    }

    private function indexExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM pim_catalog_category')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return isset($indexesIndexedByName['updated_idx']);
    }
}
