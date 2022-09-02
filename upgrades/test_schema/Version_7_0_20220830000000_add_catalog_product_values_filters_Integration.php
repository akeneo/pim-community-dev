<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220830000000_add_catalog_product_values_filters_Integration extends TestCase
{
    private const MIGRATION_NAME = '_7_0_20220830000000_add_catalog_product_values_filters';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItAddAutomationColumnOfTypeJson()
    {
        $this->removeColumn('akeneo_catalog', 'product_value_filters');

        $this->reExecuteMigration(self::MIGRATION_NAME);

        $this->assertTrue($this->hasColumn('akeneo_catalog', 'product_value_filters'));
    }

    private function removeColumn(string $table, string $column): void
    {
        if (!$this->hasColumn($table, $column)) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                ALTER TABLE $table DROP COLUMN $column;
            SQL
        );
    }

    private function hasColumn(string $table, string $column): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $table LIKE '$column';
            SQL,
        );

        return count($rows) >= 1;
    }
}
