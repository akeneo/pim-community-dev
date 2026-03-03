<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211129163800_add_index_on_is_visible_in_job_execution_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211129163800_add_index_on_is_visible_in_job_execution';

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

    public function test_it_adds_a_new_index_on_is_visible_column_to_the_job_execution_table(): void
    {
        $this->dropIndexIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->indexExists());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropIndexIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($this->indexExists());
    }

    private function dropIndexIfExists(): void
    {
        if ($this->indexExists()) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_job_execution DROP INDEX is_visible_idx;');
        }

        Assert::assertEquals(false, $this->indexExists());
    }

    private function indexExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return array_key_exists('is_visible_idx', $indexesIndexedByName);
    }
}
