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
class Version_7_0_20220907143028_add_connection_webhook_is_using_uuid_flag_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220907143028_add_connection_webhook_is_using_uuid_flag';
    private ?Connection $connection = null;

    protected function getConfiguration(): ?Configuration
    {
        return null;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_webhook_uses_uuid_column(): void
    {
        $this->removeColumn('akeneo_connectivity_connection', 'webhook_is_using_uuid');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->hasColumn('akeneo_connectivity_connection', 'webhook_is_using_uuid'));
    }

    private function removeColumn(string $table, string $column): void
    {
        if (!$this->hasColumn($table, $column)) {
            return;
        }

        $this->connection->executeQuery(
            <<<SQL
                ALTER TABLE $table DROP COLUMN $column;
            SQL
        );

        $this->assertFalse($this->hasColumn($table, $column));
    }

    private function hasColumn(string $table, string $column): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $table LIKE '$column';
            SQL,
        );

        return count($rows) >= 1;
    }
}
