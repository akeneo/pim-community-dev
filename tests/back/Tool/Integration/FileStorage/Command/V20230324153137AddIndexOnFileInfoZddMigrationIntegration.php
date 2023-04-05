<?php

namespace Akeneo\Test\Tool\Integration\FileStorage\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\FileStorageBundle\Command\V20230324153137AddIndexOnFileInfoZddMigration;
use Doctrine\DBAL\Connection;

class V20230324153137AddIndexOnFileInfoZddMigrationIntegration extends TestCase
{
    private V20230324153137AddIndexOnFileInfoZddMigration $migration;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migration = $this->get('Akeneo\Tool\Bundle\FileStorageBundle\Command\V20230324153137AddIndexOnFileInfoZddMigration');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_creates_index(): void
    {
        $this->removeIndex();

        $this->assertFalse($this->indexExists());

        $this->migration->migrate();

        $this->assertTrue($this->indexExists());
    }

    public function test_it_does_not_fail_if_index_already_exists(): void
    {
        $this->assertTrue($this->indexExists());

        $this->migration->migrate();

        $this->assertTrue($this->indexExists());
    }

    private function indexExists(): bool
    {
        $sql = <<<SQL
SHOW INDEX FROM akeneo_file_storage_file_info WHERE Key_name = 'original_filename_hash_storage_idx';
SQL;

        return 0 < $this->connection->executeQuery($sql)->rowCount();
    }

    private function removeIndex(): void
    {
        $sql = <<<SQL
DROP INDEX original_filename_hash_storage_idx ON akeneo_file_storage_file_info;
SQL;

        $this->connection->executeQuery($sql);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
