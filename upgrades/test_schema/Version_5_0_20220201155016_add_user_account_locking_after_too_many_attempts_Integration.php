<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_5_0_20220201155016_add_user_account_locking_after_too_many_attempts_Integration  extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_5_0_20220201155016_add_user_account_locking_after_too_many_attempts';

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

    public function test_it_adds_connection_attempt_information_to_the_oro_user_table(): void
    {
        $this->dropColumnIfExists();
        Assert::assertEquals(false, $this->columnExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertEquals(true, $this->columnExists());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->dropColumnIfExists();
        Assert::assertEquals(false, $this->columnExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        Assert::assertEquals(true, $this->columnExists());
    }

    private function dropColumnIfExists(): void
    {
        if ($this->columnExists()) {
            $this->connection->executeQuery('ALTER TABLE oro_user DROP COLUMN consecutive_authentication_failure_counter;');
            $this->connection->executeQuery('ALTER TABLE oro_user DROP COLUMN authentication_failure_reset_date;');
        }

        Assert::assertEquals(false, $this->columnExists());
    }

    private function columnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('oro_user');

        return isset($columns['consecutive_authentication_failure_counter'], $columns['authentication_failure_reset_date']);
    }
}
