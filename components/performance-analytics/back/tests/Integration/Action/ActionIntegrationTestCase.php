<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\PerformanceAnalytics\Integration\Action;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

abstract class ActionIntegrationTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected RouterInterface $router;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        $this->client = static::createClient(['environment' => 'test_fake', 'debug' => false]);
        $this->client->disableReboot();

        $this->router = $this->get('router');
        $this->disableSsoConfiguration();
    }

    protected function get(string $service)
    {
        return static::getContainer()->get($service);
    }

    private function disableSsoConfiguration(): void
    {
        $fakeConfigurationRepository = new class() implements Repository {
            public function save(Configuration $configurationRoot): void
            {
            }

            public function find(string $code): Configuration
            {
                throw new ConfigurationNotFound($code);
            }
        };
        static::getContainer()->set('akeneo_authentication.sso.configuration.repository', $fakeConfigurationRepository);
    }
}
