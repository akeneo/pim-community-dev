<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211207154203_add_is_trackable_in_step_execution_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211207154203_add_is_trackable_in_step_execution';

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

    public function test_it_adds_a_is_trackable_column_to_the_step_execution_table(): void
    {
        $this->dropColumnIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->columnExists());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropColumnIfExists();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        Assert::assertTrue($this->columnExists());
    }

    private function dropColumnIfExists(): void
    {
        if ($this->columnExists()) {
            $this->connection->executeQuery('ALTER TABLE akeneo_batch_step_execution DROP COLUMN is_trackable;');
        }

        Assert::assertEquals(false, $this->columnExists());
    }

    private function columnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('akeneo_batch_step_execution');

        return array_key_exists('is_trackable', $columns);
    }
}
