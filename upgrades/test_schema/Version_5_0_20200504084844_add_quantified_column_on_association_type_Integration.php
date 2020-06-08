<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200504084844_add_quantified_column_on_association_type_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200504084844_add_quantified_column_on_association_type';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_update_the_association_type_table()
    {
        $connection = $this->get('database_connection');
        $connection->executeQuery('ALTER TABLE pim_catalog_association_type DROP COLUMN is_quantified;');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertAssociationTypeTableHasColumns(['is_quantified' => 'boolean']);
        $this->assertDefaultIsQuantifiedValueIsZero();
    }

    private function assertAssociationTypeTableHasColumns(array $expectedColumnsAndTypes): void
    {
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('pim_catalog_association_type');
        $actualColumnsAndTypes = [];
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = $actualColumn->getType()->getName();
        }

        Assert::assertEquals(array_merge($actualColumnsAndTypes, $expectedColumnsAndTypes), $actualColumnsAndTypes);
    }

    private function assertDefaultIsQuantifiedValueIsZero(): void
    {
        $connection = $this->get('database_connection');
        $stmt = $connection->executeQuery('SELECT is_quantified FROM pim_catalog_association_type LIMIT 1');
        $result = $stmt->fetch();

        Assert::assertSame('0', $result['is_quantified']);
    }
}
