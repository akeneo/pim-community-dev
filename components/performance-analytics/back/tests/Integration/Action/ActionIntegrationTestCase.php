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
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
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

    public function logIn(KernelBrowser $kernelBrowser, string $username): void
    {
        $user = $this->createUser($username);
        $this->createSession($kernelBrowser, new UsernamePasswordToken($user, 'main', $user->getRoles()));
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->addRole(new Role(User::ROLE_DEFAULT));
        $user->setUsername($username);
        $this->get('pim_user.repository.user')->save($user);

        return $user;
    }

    private function createSession(KernelBrowser $client, TokenInterface $token): void
    {
        $session = $this->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
