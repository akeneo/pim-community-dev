<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200217155537_add_measurement_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200217155537_add_measurement_table';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_creates_the_measurement_table()
    {
        $this->dropMeasurementTable();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertMeasurementTableExist();
        $this->assertMeasurementTableHasColumns(
            [
                'code' => 'string',
                'labels' => 'string',
                'standard_unit' => 'string',
                'units' => 'string',
            ]
        );
        $this->assertNumberOfMeasurements(23);
    }

    private function dropMeasurementTable(): void
    {
        $this->get('database_connection')->executeQuery('DROP TABLE akeneo_measurement');
    }

    private function assertMeasurementTableExist()
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tables = $schemaManager->listTableNames();
        Assert::assertContains('akeneo_measurement', $tables);
    }

    private function assertMeasurementTableHasColumns(array $expectedColumnsAndTypes)
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_measurement');
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = $actualColumn->getType()->getName();
        }
        Assert::assertEquals($expectedColumnsAndTypes, $actualColumnsAndTypes);
    }

    private function assertNumberOfMeasurements(int $expectedNumberOfMeasurements): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $stmt = $connection->executeQuery('SELECT COUNT(*) FROM akeneo_measurement;');
        $actualNumberOfMeasurements = $stmt->fetch(\PDO::FETCH_COLUMN);
        Assert::assertEquals($expectedNumberOfMeasurements, $actualNumberOfMeasurements);
    }
}
