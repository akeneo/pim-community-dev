<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20221018000000_create_connected_channel_table_for_growth_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221018000000_create_connected_channel_table_for_growth';
    private const CONNECTED_CHANNEL_TABLE_NAME = 'akeneo_syndication_connected_channel';
    private const FAMILY_TABLE_NAME = 'akeneo_syndication_family';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_the_syndication_tables_if_not_present()
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->tableExists(self::CONNECTED_CHANNEL_TABLE_NAME));
        $this->assertTrue($this->tableExists(self::FAMILY_TABLE_NAME));
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
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
}
