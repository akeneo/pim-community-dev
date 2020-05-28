<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var UserInterface */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::$container->get('test.client');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->user = null;
    }

    protected function authenticateAsAdmin()
    {
        $this->authenticate($this->getAdminUser());
    }

    protected function getAdminUser(): UserInterface
    {
        if (!$this->user) {
            $this->user = self::$container->get('pim_user.manager')->findUserByUsername('admin') ?? $this->createAdminUser();
        }

        return $this->user;
    }

    private function authenticate(UserInterface $user)
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

    private function getSession(): SessionInterface
    {
        return $this->client->getContainer()->get('session');
    }
}
