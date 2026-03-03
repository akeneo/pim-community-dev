<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::getContainer()->get('test.client');
    }

    protected function authenticateAsAdmin(): UserInterface
    {
        $user = $this->createAdminUser();

        $this->authenticate($user);

        return $user;
    }

    private function authenticate(UserInterface $user)
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

    private function getSession(): SessionInterface
    {
        return $this->client->getContainer()->get('session');
    }
}
