<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
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

    protected function createReferenceEntity(string $referenceEntityCode, array $labels): void
    {
        $isRefEntityFFenabled = $this->getFeatureFlag()->isEnabled('reference_entity');
        if (!$isRefEntityFFenabled) {
            $this->getFeatureFlag()->enable('reference_entity');
        }

        /** @phpstan-ignore-next-line */
        $createReferenceEntityCommand = new CreateReferenceEntityCommand($referenceEntityCode, $labels);
        $validator = $this->get('validator');
        $violations = $validator->validate($createReferenceEntityCommand);
        Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
        ($this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler'))(
            $createReferenceEntityCommand
        );
    }

    protected function createRecords(string $referenceEntityCode, array $recordCodes): void
    {
        $validator = $this->get('validator');
        foreach ($recordCodes as $recordCode) {
            /** @phpstan-ignore-next-line */
            $createRecord = new CreateRecordCommand($referenceEntityCode, $recordCode, []);
            $violations = $validator->validate($createRecord);
            Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
            ($this->get('akeneo_referenceentity.application.record.create_record_handler'))($createRecord);
        }
    }

    protected function createAttribute(array $data): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $attributeViolations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $attributeViolations, \sprintf('The attribute is invalid: %s', $attributeViolations));
        $this->getAttributeSaver()->save($attribute);

        return $attribute;
    }

    private function getFeatureFlag(): FeatureFlags
    {
        return $this->get('feature_flags');
    }

    private function getAttributeSaver(): AttributeSaver
    {
        return $this->get('pim_catalog.saver.attribute');
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
