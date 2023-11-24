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
class Version_6_0_20211124163100_add_index_to_improve_search_on_job_execution_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211124163100_add_index_to_improve_search_on_job_execution';

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
        $this->dropIndexesIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->indexesExists());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropIndexesIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        Assert::assertTrue($this->indexesExists());
    }

    private function dropIndexesIfExists(): void
    {
        if ($this->indexExists('user_idx')) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP INDEX user_idx;');
        }

        if ($this->indexExists('status_idx')) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP INDEX status_idx;');
        }

        if ($this->indexExists('start_time_idx')) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP INDEX start_time_idx;');
        }

        Assert::assertEquals(false, $this->indexesExists());
    }

    private function indexesExists(): bool
    {
        return $this->indexExists('user_idx') && $this->indexExists('status_idx') && $this->indexExists('start_time_idx');
    }

    private function indexExists(string $indexName): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return isset($indexesIndexedByName[$indexName]);
    }
}
