<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration;

use Akeneo\Platform\Job\Test\Integration\Loader\FixturesLoader;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    protected FixturesLoader $fixturesLoader;
    private Connection $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        $this->dbalConnection = $this->get('database_connection');
        $this->fixturesLoader = $this->get('Akeneo\Platform\Job\Test\Integration\Loader\FixturesLoader');
        $this->fixturesLoader->resetFixtures();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
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
