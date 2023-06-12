<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerIntegrationTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected WebClientHelper $webClientHelper;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        self::ensureKernelShutdown();
        $this->client = static::createClient(['debug' => false]);
        $this->client->disableReboot();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
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
}
