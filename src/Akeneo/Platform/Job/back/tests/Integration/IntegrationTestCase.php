<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration;

use Akeneo\Platform\Job\Test\Integration\Loader\FixturesJobHelper;
use Akeneo\Platform\Job\Test\Integration\Loader\FixturesLoader;
use Akeneo\Platform\Job\Test\Integration\Loader\FixturesUserHelper;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    protected FixturesJobHelper $fixturesJobHelper;
    protected FixturesUserHelper $fixturesUserHelper;
    protected FixturesLoader $fixturesLoader;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        $this->fixturesJobHelper = $this->get(FixturesJobHelper::class);
        $this->fixturesUserHelper = $this->get(FixturesUserHelper::class);
        $this->fixturesLoader = $this->get(FixturesLoader::class);
        $this->fixturesLoader->resetFixtures();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service): ?object
    {
        return self::getContainer()->get($service);
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
