<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230131155436_add_column_is_deactivated_on_template_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230131155436_add_column_is_deactivated_on_template';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_is_deactivated_column()
    {
        if ($this->hasIsDeactivatedColumn()) {
            $this->dropIsDeactivatedColumn();
        }

        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertTrue($this->hasIsDeactivatedColumn());
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function hasIsDeactivatedColumn(): bool
    {
        return $this->connection->executeQuery(
            <<<SQL
                SHOW COLUMNS FROM pim_catalog_category_template LIKE 'is_deactivated';
            SQL,
        )->rowCount() >= 1;
    }

    private function dropIsDeactivatedColumn(): void
    {
        $sql = <<<SQL
            ALTER TABLE pim_catalog_category_template DROP COLUMN is_deactivated;
        SQL;

        $this->connection->executeQuery($sql);
    }
}
