<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200324104437_migrate_measurement_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200324104437_migrate_measurement_table';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_updates_the_measurement_table()
    {
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
        $this->assertUnitConvertionsDoesNotContainScientificNotation();
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

    private function assertUnitConvertionsDoesNotContainScientificNotation(): void
    {
        $operationValuesSql = <<<SQL
SELECT JSON_ARRAYAGG(operation_value) AS concatenated_operations
FROM (
  SELECT JSON_EXTRACT(units, "$[*].convert_from_standard[*].value") as operation_value
  FROM akeneo_measurement as raw_json_value
) as operation_values
SQL;
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $operationValues = json_decode($connection->fetchAll($operationValuesSql)[0]['concatenated_operations']);

        foreach ($operationValues as $operationValue) {
            foreach($operationValue as $value) {
                Assert::assertIsNumeric($value);
            }
        }
    }
}
