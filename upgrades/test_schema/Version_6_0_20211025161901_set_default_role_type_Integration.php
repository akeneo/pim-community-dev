<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class Version_6_0_20211025161901_set_default_role_type_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211025161901_set_default_role_type';

    public function test_it_adds_a_default_role_type_to_oro_access_role(): void
    {
        $connection = $this->getConnection();

        $connection->executeQuery('ALTER TABLE oro_access_role MODIFY type VARCHAR(30) DEFAULT NULL;');
        $connection->executeQuery('UPDATE oro_access_role SET type = NULL;');

        foreach($this->getAllRoleTypes() as $type) {
            Assert::assertNull($type);
        }

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        foreach($this->getAllRoleTypes() as $type) {
            Assert::assertEquals('default', $type);
        }

        $columns = $this->getConnection()->getSchemaManager()->listTableColumns('oro_access_role');
        Assert::assertEquals('default', $columns['type']->getDefault());
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

    private function getAllRoleTypes(): array
    {
        $query = <<<SQL
SELECT type
FROM oro_access_role
SQL;

        return array_column($this->getConnection()->fetchAllAssociative($query), 'type');
    }
}
