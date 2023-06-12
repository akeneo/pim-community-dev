<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ControllerIntegrationTestCase extends WebTestCase
{
    protected CatalogInterface $catalog;
    protected KernelBrowser $client;
    protected WebClientHelper $webClientHelper;
    protected FilePersistedFeatureFlags $featureFlags;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->getConfiguration());

        self::ensureKernelShutdown();
        $this->client = static::createClient(['debug' => false]);
        $this->client->disableReboot();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->featureFlags = $this->get('feature_flags');
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
        $this->ensureKernelShutdown();
    }

    protected function logAs(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    abstract protected function getConfiguration(): Configuration;
}
