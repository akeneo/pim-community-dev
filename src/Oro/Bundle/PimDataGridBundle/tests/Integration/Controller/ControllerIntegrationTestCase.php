<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class ControllerIntegrationTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected CatalogInterface $catalog;
    private RouterInterface $router;

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->router = $this->get('router');
        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->getConfiguration());

        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    public function callRoute(
        KernelBrowser $client,
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $headers = [],
        array $parameters = [],
        string $content = null
    ): void {
        $url = $this->router->generate($route, $routeArguments);
        $client->request($method, $url, $parameters, [], $headers, $content);
    }

    protected function callApiRoute(
        KernelBrowser $client,
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $parameters = [],
        string $content = null
    ): void {
        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json',
        ];
        $url = $this->router->generate($route, $routeArguments);
        $client->request($method, $url, $parameters, [], $headers, $content);
    }

    protected function logIn(string $username): void
    {
        $session = $this->get('session');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        if (null === $user) {
            $user = $this->createUser($username);
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Create a token with a user with all access.
     */
    private function createUser(string $username): User
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(uniqid());
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    protected function assertStatusCode(Response $response, int $statusCode): void
    {
        Assert::assertSame($statusCode, $response->getStatusCode(), sprintf(
            'Expected response status code is not the same as the actual. Failed with content %s',
            $response->getContent()
        ));
    }
}
