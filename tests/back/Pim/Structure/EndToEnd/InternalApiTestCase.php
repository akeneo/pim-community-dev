<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\EndToEnd;

use Akeneo\Test\Integration\TestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class InternalApiTestCase extends TestCase
{
    /** @var HttpKernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getClient();
    }

    protected function authenticate(UserInterface $user): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session = $this->getSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function authenticateAsAdminUser()
    {
        $this->authenticate($this->getAdminUser());
    }

    private function getAdminUser()
    {
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    private function getClient(): HttpKernelBrowser
    {
        return self::$container->get('test.client');
    }

    private function getSession(): SessionInterface
    {
        return self::$container->get('session');
    }
}
