<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20210505180000_add_updated_to_category_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210505180000_add_updated_to_category';

    public function test_it_adds_a_new_updated_column_to_the_category_table(): void
    {
        $connection = $this->getConnection();

        $connection->executeQuery('ALTER TABLE pim_catalog_category DROP COLUMN updated;');
        Assert::assertFalse($this->updatedColumnExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->updatedColumnExists());

        $row = $connection->executeQuery('SELECT * from pim_catalog_category')->fetchAssociative();
        Assert::assertEquals($row['created'], $row['updated']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function updatedColumnExists(): bool
    {
        $columns = $this->getConnection()->getSchemaManager()->listTableColumns('pim_catalog_category');

        return isset($columns['updated']);
    }
}
