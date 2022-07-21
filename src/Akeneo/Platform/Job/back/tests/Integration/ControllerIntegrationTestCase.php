<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration;

use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class ControllerIntegrationTestCase extends IntegrationTestCase
{
    protected KernelBrowser $client;
    protected WebClientHelper $webClientHelper;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = static::createClient(['debug' => false]);
        $this->client->disableReboot();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    protected function logAs(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }
}
