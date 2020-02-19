<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Integration\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Installer\MeasurementsInstaller;
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
    /** @var MeasurementsInstaller */
    protected $fixturesLoader;

    /** @var Connection */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->fixturesLoader = $this->get('akeneo_measure.installer.measurement_installer');
        $this->connection = $this->get('doctrine.dbal.default_connection');

        $this->resetDB();
        $this->fixturesLoader->createSchema();


        $sql = <<<SQL
INSERT INTO `akeneo_measurement` (`code`, `labels`, `standard_unit`, `units`)
VALUES
	('Area', '{\"en_US\": \"Area\", \"fr_FR\": \"Surface\"}', 'SQUARE_MILLIMETER', '[{\"code\": \"SQUARE_MILLIMETER\", \"labels\": {\"en_US\": \"Square millimeter\", \"fr_FR\": \"Millimètre carré\"}, \"symbol\": \"mm²\", \"convert\": [{\"value\": \"0.000001\", \"operator\": \"mul\"}]}, {\"code\": \"SQUARE_CENTIMETER\", \"labels\": {\"en_US\": \"Square centimeter\", \"fr_FR\": \"Centimètre carré\"}, \"symbol\": \"cm²\", \"convert\": [{\"value\": \"0.0001\", \"operator\": \"mul\"}]}]'),
	('Binary', '{\"en_US\": \"Binary\", \"fr_FR\": \"Binaire\"}', 'BYTE', '[{\"code\": \"BIT\", \"labels\": {\"en_US\": \"Bit\", \"fr_FR\": \"Bit\"}, \"symbol\": \"b\", \"convert\": [{\"value\": \"0.125\", \"operator\": \"mul\"}]}, {\"code\": \"BYTE\", \"labels\": {\"en_US\": \"Byte\", \"fr_FR\": \"Octet\"}, \"symbol\": \"B\", \"convert\": [{\"value\": \"1\", \"operator\": \"mul\"}]}]');
SQL;
        $this->connection->executeQuery($sql);
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
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
