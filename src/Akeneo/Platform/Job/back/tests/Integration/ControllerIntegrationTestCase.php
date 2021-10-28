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
        $this->client = static::createClient(['debug' => false]);
        $this->client->disableReboot();
    }
}
