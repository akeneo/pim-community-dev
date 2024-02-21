<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\EndToEnd;

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

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        $session = $this->getSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getClient(): HttpKernelBrowser
    {
        return self::getContainer()->get('test.client');
    }

    private function getSession(): SessionInterface
    {
        return self::getContainer()->get('session');
    }

    protected function getAdminUser(): UserInterface
    {
        return self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
