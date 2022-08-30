<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\Installer;

use Akeneo\Tool\Bundle\MeasureBundle\Installer\MeasurementInstaller;
use Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementInstallerIntegration extends SqlIntegrationTestCase
{
    private ?MeasurementInstaller $measurementInstaller = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->measurementInstaller = $this->get('akeneo_measure.installer.measurement_installer');
    }

    /**
     * @test
     */
    public function it_installs_the_measurements_database_and_some_standard_measurements_families()
    {
        $this->dropMeasurementTable();

        $this->measurementInstaller->createMeasurementTableAndStandardMeasurementFamilies();

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
        $actualColumnsAndTypes = [];
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_measurement');
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] =  $actualColumn->getType()->getName();
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
