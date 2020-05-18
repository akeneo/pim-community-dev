<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200514064843_add_create_and_update_date_in_asset_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200514064843_add_create_and_update_date_in_asset';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_update_the_assert_manager_asset_table()
    {
        $connection = $this->get('database_connection');
        $connection->executeQuery('ALTER TABLE akeneo_asset_manager_asset DROP COLUMN created_at;');
        $connection->executeQuery('ALTER TABLE akeneo_asset_manager_asset DROP COLUMN updated_at;');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertAssetManagerAssetTableHasColumns(['created_at' => 'datetime', 'updated_at' => 'datetime']);
    }

    private function assertAssetManagerAssetTableHasColumns(array $expectedColumnsAndTypes): void
    {
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_asset_manager_asset');
        $actualColumnsAndTypes = [];
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = $actualColumn->getType()->getName();
        }

        Assert::assertEquals(array_merge($actualColumnsAndTypes, $expectedColumnsAndTypes), $actualColumnsAndTypes);
    }
}
