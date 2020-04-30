<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200429094251_add_quantified_associations_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200429094251_add_quantified_associations';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_updates_the_product_and_product_model_table()
    {
        $connection = $this->get('database_connection');
        $connection->executeQuery('ALTER TABLE pim_catalog_product DROP quantified_associations;');
        $connection->executeQuery('ALTER TABLE pim_catalog_product_model DROP quantified_associations;');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertProductTableHasColumns(['quantified_associations' => 'string']);
        $this->assertProductModelTableHasColumns(['quantified_associations' => 'string']);
    }

    private function assertProductTableHasColumns(array $expectedColumnsAndTypes)
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('pim_catalog_product');
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = $actualColumn->getType()->getName();
        }
        Assert::assertEquals(array_merge($actualColumnsAndTypes, $expectedColumnsAndTypes), $actualColumnsAndTypes);
    }

    private function assertProductModelTableHasColumns(array $expectedColumnsAndTypes)
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('pim_catalog_product_model');
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = $actualColumn->getType()->getName();
        }
        Assert::assertEquals(array_merge($actualColumnsAndTypes, $expectedColumnsAndTypes), $actualColumnsAndTypes);
    }
}
