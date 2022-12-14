<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
abstract class ControllerEndToEndTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected CatalogInterface $catalog;

    private const DEFAULT_HEADER = [
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
    ];

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        /** @var FilePersistedFeatureFlags $featureFlags*/
        $featureFlags = $this->get('feature_flags');
        $featureFlags->deleteFile();
        $featureFlags->enable('identifier_generator');
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFeatureFlagsBeforeInstall() as $featureFlag) {
            $featureFlags->enable($featureFlag);
        }
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);

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

    protected function callGetRouteWithQueryParam(string $routeName, array $queryParam, ?array $header = self::DEFAULT_HEADER): void
    {
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
        /** @var WebClientHelper $webClientHelper */
        $webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');

        return $webClientHelper;
    }

    protected function removeAclFromRole(string $aclPrivilegeIdentityId, string $role = 'ROLE_ADMINISTRATOR'): void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $role = $this->get('pim_user.repository.role')->findOneByIdentifier($role);
        $privilege = new AclPrivilege();
        $identity = new AclPrivilegeIdentity($aclPrivilegeIdentityId);
        $privilege
            ->setIdentity($identity)
            ->addPermission(new AclPermission('EXECUTE', AccessLevel::NONE_LEVEL));
        $aclManager->getPrivilegeRepository()->savePrivileges(
            $aclManager->getSid($role),
            new ArrayCollection([$privilege])
        );

        $aclManager->flush();
        $aclManager->clearCache();
    }
}
