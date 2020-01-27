<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::$container->get('test.client');
    }

    protected function createConnection(string $code, string $label, string $flowType): ConnectionWithCredentials
    {
        $createConnectionCommand = new CreateConnectionCommand($code, $label, $flowType);

        return $this->get('akeneo_connectivity.connection.application.handler.create_connection')
            ->handle($createConnectionCommand);
    }

    protected function authenticateAsAdmin()
    {
        $user = $this->createAdminUser();

        $this->authenticate($user);
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
