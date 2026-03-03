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
class Version_6_0_20211130113100_add_index_to_improve_search_on_job_instance_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211130113100_add_index_to_improve_search_on_job_instance';

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

    public function test_it_adds_new_index_on_job_execution_table(): void
    {
        $this->dropIndexIfExists('code_idx');

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->indexExists('code_idx'));
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropIndexIfExists('code_idx');

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->indexExists('code_idx'));
    }

    private function dropIndexIfExists(string $indexName): void
    {
        if ($this->indexExists($indexName)) {
            $this->connection->executeQuery(sprintf('ALTER TABLE akeneo_batch_job_instance DROP INDEX %s;', $indexName));
        }

        Assert::assertEquals(false, $this->indexExists($indexName));
    }

    private function indexExists(string $indexName): bool
    {
        $indices = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_instance')->fetchAllAssociative();
        $indicesIndexedByName = array_column($indices, null, 'Key_name');

        return array_key_exists($indexName, $indicesIndexedByName);
    }
}
