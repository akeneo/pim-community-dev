<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class Version_7_0_20220111161207_update_author_to_be_nullable_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220111161207_update_author_to_be_nullable';

    public function test_it_modify_columns_and_keep_the_data(): void
    {
        $query = <<<SQL
        ALTER TABLE akeneo_connectivity_connected_app
        MODIFY author varchar(255) NOT NULL;
SQL;

        $this->getConnection()->executeQuery($query);
        $this->assertAuthorColumnIsNullable(false);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertAuthorColumnIsNullable(true);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertAuthorColumnIsNullable(bool $isNullable): void
    {
        $columns = $this->getConnection()->getSchemaManager()->listTableColumns('akeneo_connectivity_connected_app');

        Assert::assertEquals(!$isNullable, $columns['author']?->getNotnull());
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
