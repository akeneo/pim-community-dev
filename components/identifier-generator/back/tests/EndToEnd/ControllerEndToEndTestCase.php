<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
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

    protected const DEFAULT_HEADER = [
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
    ];

    private function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        /** @var FilePersistedFeatureFlags $featureFlags */
        $featureFlags = $this->get('feature_flags');
        $featureFlags->deleteFile();
        $featureFlags->enable('identifier_generator');
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFeatureFlagsBeforeInstall() as $featureFlag) {
            $featureFlags->enable($featureFlag);
        }
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);

        $this->initAcls();
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

    protected function callRoute(string $routeName, ?array $header = self::DEFAULT_HEADER, $routeParams = []): void
    {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            $routeParams,
            'GET',
            $header
        );
    }

    protected function callGetRouteWithQueryParam(
        string $routeName,
        array $queryParam,
        ?array $header = self::DEFAULT_HEADER
    ): void {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            $queryParam,
            'GET',
            $header
        );
    }

    protected function callUpdateRoute(
        string $routeName,
        array $routeArguments,
        ?array $header = self::DEFAULT_HEADER,
        string $content = ''
    ): void {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            $routeArguments,
            'PATCH',
            $header,
            [],
            $content
        );
    }

    protected function callDeleteRoute(
        string $routeName,
        ?array $routeArguments = [],
        ?array $header = self::DEFAULT_HEADER
    ): void {
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

    protected function callGetRoute(
        string $routeName,
        string $code = '',
        ?array $header = self::DEFAULT_HEADER
    ): void {
        $this->getWebClientHelper()->callRoute(
            $this->client,
            $routeName,
            ['code' => $code],
            'GET',
            $header
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
        return $this->get('akeneo_integration_tests.helper.web_client');
    }

    private function initAcls(): void
    {
        $acls = [
            'ROLE_ADMINISTRATOR' => [
                'action:pim_identifier_generator_manage' => true,
                'action:pim_identifier_generator_view' => true,
            ],
            'ROLE_CATALOG_MANAGER' => [
                'action:pim_identifier_generator_manage' => true,
                'action:pim_identifier_generator_view' => false,
            ],
            'ROLE_USER' => [
                'action:pim_identifier_generator_manage' => false,
                'action:pim_identifier_generator_view' => true,
            ],
            'ROLE_TRAINEE' => [
                'action:pim_identifier_generator_manage' => false,
                'action:pim_identifier_generator_view' => false,
            ],
        ];

        foreach ($acls as $role => $newPermissions) {
            $this->setAcls($role, $newPermissions);
        }
    }

    public function setAcls(string $role, array $newPermissions): void
    {
        $roleWithPermissions = $this->get('pim_user.repository.role_with_permissions')->findOneByIdentifier($role);
        $roleWithPermissions->setPermissions(\array_merge($roleWithPermissions->permissions(), $newPermissions));

        $this->get('pim_user.saver.role_with_permissions')->saveAll([$roleWithPermissions]);
    }
}
