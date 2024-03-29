<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Helper;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

abstract class ControllerEndToEndTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected CatalogInterface $catalog;
    private RouterInterface $router;
    private AuthenticatorHelper $authenticatorHelper;

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->router = $this->get('router');
        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        $this->authenticatorHelper = $this->get('akeneo_integration_tests.helper.authenticator');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->getConfiguration());
        /** @var SystemUserAuthenticator $systemAuthenticator */
        $systemAuthenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $systemAuthenticator->createSystemUser();
        /** @var UnitOfWorkAndRepositoriesClearer $cacheCLearer */
        $cacheCLearer = $this->get('pim_connector.doctrine.cache_clearer');
        $cacheCLearer->clear();
    }

    protected function get(string $service): ?object
    {
        return self::getContainer()->get($service);
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

    /**
     * @param array<string, string>|array<empty> $routeArguments
     * @param array<string, string>|array<empty> $headers
     * @param array<string, string>|array<empty> $parameters
     */
    public function callRoute(
        KernelBrowser $client,
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $headers = [],
        array $parameters = [],
        string $content = null,
    ): void {
        $url = $this->router->generate($route, $routeArguments);
        $client->request($method, $url, $parameters, [], $headers, $content);
    }

    /**
     * @param array<string, string>|array<empty> $routeArguments
     * @param array<string, string>|array<empty> $parameters
     */
    protected function callApiRoute(
        KernelBrowser $client,
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $parameters = [],
        string $content = null,
    ): void {
        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json',
        ];
        $url = $this->router->generate($route, $routeArguments);
        $client->request($method, $url, $parameters, [], $headers, $content);
    }

    protected function logAs(string $username): void
    {
        $this->authenticatorHelper->logIn($username, $this->client);
    }
}
