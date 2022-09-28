<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20222809090000_rename_requirements_column_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20222809090000_rename_requirements_column';
    private const FAMILY_TABLE_NAME = 'akeneo_syndication_family';
    private const COLUMN_NAME = 'requirements';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_renames_the_requirements_column()
    {
        $this->renameColumnToData();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->columnExists(self::COLUMN_NAME));
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function columnExists(string $columnName): bool
    {
        return $this->connection->executeQuery(
            'SHOW COLUMNS FROM `akeneo_syndication_family` LIKE :columnName',
            [
                'columnName' => $columnName,
            ]
        )->rowCount() >= 1;
    }

    private function renameColumnToData()
    {
        $sql = <<<SQL
            ALTER TABLE `akeneo_syndication_family`
            RENAME COLUMN `requirements` TO `data`;
        SQL;

        $this->connection->executeQuery($sql);
    }
}
