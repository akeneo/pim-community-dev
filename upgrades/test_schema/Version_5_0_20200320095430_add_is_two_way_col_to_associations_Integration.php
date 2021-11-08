<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200320095430_add_is_two_way_col_to_associations_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200320095430_add_is_two_way_col_to_associations';

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
        $connection->executeQuery('ALTER TABLE pim_catalog_association_type DROP COLUMN is_two_way;');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertAssociationTypeTableHasColumns(['is_two_way' => 'boolean']);
        $this->assertDefaultTwoWayValueIsZero();
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

    private function assertDefaultTwoWayValueIsZero(): void
    {
        $connection = $this->get('database_connection');
        $stmt = $connection->executeQuery('SELECT is_two_way FROM pim_catalog_association_type LIMIT 1');
        $result = $stmt->fetchAssociative();

        Assert::assertSame('0', $result['is_two_way']);
    }
}
