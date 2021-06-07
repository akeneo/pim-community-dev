<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class Version_6_0_20210524121906_add_profile_to_user_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210524121906_add_profile_to_user';

    public function test_it_adds_a_profile_column_to_the_oro_user_table(): void
    {
        $connection = $this->getConnection();

        $connection->executeQuery('ALTER TABLE oro_user DROP COLUMN profile;');
        Assert::assertFalse($this->updatedColumnExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->updatedColumnExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function updatedColumnExists(): bool
    {
        $columns = $this->getConnection()->getSchemaManager()->listTableColumns('oro_user');

        return isset($columns['profile']);
    }
}
