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
final class Version_8_0_20230308145407_add_sort_order_for_identifier_generator_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230308145407_add_sort_order_for_identifier_generator';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_sort_order_column()
    {
        if ($this->hasSortOrderColumn()) {
            $this->dropSortOrderColumn();
        }

        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertTrue($this->hasSortOrderColumn());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function hasSortOrderColumn(): bool
    {
        return $this->connection->executeQuery(
            <<<SQL
                SHOW COLUMNS FROM pim_catalog_identifier_generator LIKE 'sort_order';
            SQL,
        )->rowCount() >= 1;
    }

    private function dropSortOrderColumn(): void
    {
        $sql = <<<SQL
            ALTER TABLE pim_catalog_identifier_generator DROP COLUMN sort_order;
        SQL;

        $this->connection->executeQuery($sql);
    }
}
