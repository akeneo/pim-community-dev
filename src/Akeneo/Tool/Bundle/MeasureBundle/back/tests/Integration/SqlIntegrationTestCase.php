<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration;

use Akeneo\Tool\Bundle\MeasureBundle\Installer\MeasurementInstaller;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class is used for running integration tests testing the SQL implementation of query functions and repositories.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SqlIntegrationTestCase extends KernelTestCase
{
    protected ?MeasurementInstaller $fixturesLoader = null;
    protected ?Connection $connection = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->fixturesLoader = $this->get('akeneo_measure.installer.measurement_installer');
        $this->connection = $this->get('doctrine.dbal.default_connection');

        $this->resetDB();
        $this->fixturesLoader->createMeasurementTableAndStandardMeasurementFamilies();
    }

    protected function get(string $service)
    {
        return self::getContainer()->get($service);
    }

    protected function resetDB(): void
    {
        $sql = <<<SQL
DROP TABLE akeneo_measurement;
SQL;
        $this->connection->executeQuery($sql);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }
}
