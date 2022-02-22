<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Integration;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class is used for running integration tests.
 * It should be used for testing the SQL implementation of query functions and repositories.
 */
abstract class SqlIntegrationTestCase extends KernelTestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->connection = $this->get('doctrine.dbal.default_connection');

        $this->resetDatabase();
    }

    protected function get(string $service)
    {
        return static::getContainer()->get($service);
    }

    protected function resetDatabase(): void
    {
        $sql = <<<SQL
            DROP TABLE IF EXISTS `akeneo_onboarder_serenity_supplier`;
        SQL;

        $this->connection->executeQuery($sql);
    }

    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }
}
