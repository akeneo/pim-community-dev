<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd;

use Akeneo\Test\Integration\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
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

    protected function authenticate($username, $password)
    {
        $token = new UsernamePasswordToken('admin', 'admin', 'main', ['ROLE_ADMINISTRATOR']);

        $session = $this->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
