<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        Assert::assertTrue($this->indexesExist());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropIndexesIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->indexesExist());
    }

    private function dropIndexesIfExists(): void
    {
        if ($this->indexExist('user_idx')) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP INDEX user_idx;');
        }

        if ($this->indexExist('status_idx')) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP INDEX status_idx;');
        }

        Assert::assertEquals(false, $this->indexesExist());
    }

    private function indexesExist(): bool
    {
        return $this->indexExist('user_idx') && $this->indexExist('status_idx');
    }

    private function indexExist(string $indexName): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexIndexedByName = array_column($indexes, null, 'Key_name');

        return isset($indexIndexedByName[$indexName]);
    }
}
