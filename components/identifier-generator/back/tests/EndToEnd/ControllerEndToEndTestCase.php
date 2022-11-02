<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
abstract class ControllerEndToEndTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected CatalogInterface $catalog;

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        if (null !== $this->getConfiguration()) {
            $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
            $fixturesLoader->load($this->getConfiguration());
        }

        // authentication should be done after loading the database as the user is created with first activated locale as default locale
        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
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

    protected function loginAs(string $username): void
    {
        $this->getAuthenticated()->logIn($username, $this->client);
    }

    private const DEFAULT_HEADER = [
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
    ];

    protected function callRoute(string $routeName, ?array $header = self::DEFAULT_HEADER): void
    {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            [],
            'GET',
            $header
        );
    }

    protected function callDeleteRoute(string $routeName, ?array $routeArguments = [], ?array $header = self::DEFAULT_HEADER): void
    {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            $routeArguments,
            'DELETE',
            $header
        );
    }

    protected function callCreateRoute(
        string $routeName,
        ?array $header = self::DEFAULT_HEADER,
        string $content = ''
    ): void {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            [],
            'POST',
            $header,
            [],
            $content
        );
    }

    private function getAuthenticated(): AuthenticatorHelper
    {
        /** @var AuthenticatorHelper $authenticatorHelper */
        $authenticatorHelper = $this->get('akeneo_integration_tests.helper.authenticator');

        return $authenticatorHelper;
    }

    private function getWebClientHelper(): WebClientHelper
    {
        /** @var WebClientHelper $webClientHelper */
        $webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');

        return $webClientHelper;
    }
}
