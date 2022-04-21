<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration;

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

        $this->connection->executeStatement(<<<SQL
            DELETE FROM `akeneo_onboarder_serenity_supplier`;
            DELETE FROM `akeneo_onboarder_serenity_supplier_contributor`;
            DELETE FROM `akeneo_batch_job_execution`;
        SQL);
    }

    protected function get(string $service): ?object
    {
        return static::getContainer()->get($service);
    }

    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }
}
