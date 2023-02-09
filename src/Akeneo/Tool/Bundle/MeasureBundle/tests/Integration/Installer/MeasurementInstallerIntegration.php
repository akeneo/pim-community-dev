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
            // KLUDGE: getting the type name is now deprecated and there is no replacement in DBAL
            // so we have to check the class it returns. Hopefuly there will be a better way in the future
            [
                'code' => 'Doctrine\\DBAL\\Types\\StringType',
                'labels' => 'Doctrine\\DBAL\\Types\\StringType',
                'standard_unit' => 'Doctrine\\DBAL\\Types\\StringType',
                'units' => 'Doctrine\\DBAL\\Types\\StringType',
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
        $schemaManager = $this->get('database_connection')->createSchemaManager();
        $tables = $schemaManager->listTableNames();
        Assert::assertContains('akeneo_measurement', $tables);
    }

    private function assertMeasurementTableHasColumns(array $expectedColumnsAndTypes)
    {
        $actualColumnsAndTypes = [];
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->get('database_connection')->createSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_measurement');
        foreach ($tableColumns as $actualColumn) {
            $actualColumnsAndTypes[$actualColumn->getName()] = get_class($actualColumn->getType());
        }
        Assert::assertEquals($expectedColumnsAndTypes, $actualColumnsAndTypes);
    }

    private function assertNumberOfMeasurements(int $expectedNumberOfMeasurements): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $stmt = $connection->executeQuery('SELECT COUNT(*) FROM akeneo_measurement;');
        $actualNumberOfMeasurements = $stmt->fetchOne();
        Assert::assertEquals($expectedNumberOfMeasurements, $actualNumberOfMeasurements);
    }
}
