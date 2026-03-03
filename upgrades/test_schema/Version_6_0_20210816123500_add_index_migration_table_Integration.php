<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20210816123500_add_index_migration_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210816123500_add_index_migration_table';

    public function test_it_creates_the_index_migration_table(): void
    {
        $connection = $this->get('database_connection');
        $schemaManager  = $connection->getSchemaManager();

        $connection->executeQuery('DROP TABLE IF EXISTS pim_index_migration');
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertTrue($schemaManager->tablesExist('pim_index_migration'));
        $expectedColumnsAndTypes = [
            'index_alias' => 'string',
            'hash' => 'string',
            'values' => 'string',
        ];

        $tableColumns = $schemaManager->listTableColumns('pim_index_migration');
        $this->assertCount(count($expectedColumnsAndTypes), $tableColumns);

        $actualColumnsAndTypes = [];
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] =  $actualColumn->getType()->getName();
        }

        Assert::assertEquals($expectedColumnsAndTypes, $actualColumnsAndTypes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
