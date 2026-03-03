<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_7_0_20230213153500_remove_index_on_product_uuid_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20230213153500_remove_index_on_product_uuid';

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

    public function test_it_adds_new_indexes_on_job_execution_table(): void
    {
        $this->addIndexIfNotExists();
        Assert::assertTrue($this->indexExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertFalse($this->indexExists());
    }

    private function addIndexIfNotExists(): void
    {
        if (!$this->indexExists()) {
            $this->connection->executeQuery('ALTER TABLE `pim_catalog_product` ADD INDEX `product_uuid` (`uuid`);');
        }
    }

    private function indexExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM pim_catalog_product')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return isset($indexesIndexedByName['product_uuid']);
    }
}
