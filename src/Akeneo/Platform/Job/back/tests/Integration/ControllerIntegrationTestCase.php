<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class ControllerIntegrationTestCase extends IntegrationTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();
    }
}
