<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SwitchMainIdentifierControllerEndToEnd extends WebTestCase
{
    private const ROUTE = 'pim_enrich_attribute_rest_switch_main_identifier';
    private const HEADERS = [
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
        'CONTENT_TYPE' => 'application/json',
    ];
    private WebClientHelper $webClientHelper;
    private KernelBrowser $client;

    public function test_it_should_redirect_on_non_xhr_request(): void
    {
        $this->createAttribute('newIdentifier', AttributeTypes::IDENTIFIER);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [
                'attributeCode' => 'newIdentifier',
            ],
            'POST',
            []
        );
        $response = $this->client->getResponse();
        Assert::assertTrue($response->isRedirect('/'));
    }

    public function test_it_should_denied_access_if_user_is_not_granted(): void
    {
        $this->createAttribute('newIdentifier', AttributeTypes::IDENTIFIER);

        $roleWithPermissions = $this->getRoleRepository()->findOneByIdentifier('ROLE_CATALOG_MANAGER');
        $roleWithPermissions->setPermissions(\array_merge($roleWithPermissions->permissions(), [
            'action:pim_enrich_attribute_edit' => false
        ]));
        $this->getRoleSaver()->saveAll([$roleWithPermissions]);

        $this->logIn('Julia');

        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [
                'attributeCode' => 'newIdentifier',
            ],
            'POST',
            self::HEADERS
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function test_it_should_be_a_bad_request_with_unknown_attribute(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [
                'attributeCode' => 'unknownAttribute',
            ],
            'POST',
            self::HEADERS
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_it_should_be_a_bad_request_with_non_identifier_attribute(): void
    {
        $this->createAttribute('newIdentifier', AttributeTypes::TEXT);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [
                'attributeCode' => 'newIdentifier',
            ],
            'POST',
            self::HEADERS
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_it_should_be_a_bad_request_if_attribute_is_already_the_main_identifier(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [
                'attributeCode' => 'sku',
            ],
            'POST',
            self::HEADERS
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_it_is_a_success()
    {
        $this->createAttribute('newIdentifier', AttributeTypes::IDENTIFIER);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [
                'attributeCode' => 'newIdentifier',
            ],
            'POST',
            self::HEADERS
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $catalogs = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($catalogs->useTechnicalCatalog());

        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();
        $this->logIn('admin');
        $this->getCacheClearer()->clear();
        $this->webClientHelper = $this->getWebClientHelper();
    }

    private function get(string $service)
    {
        return self::getContainer()->get($service);
    }

    private function createAttribute(
        string $code,
        string $type,
    ): void {
        $attribute = $this->getAttributeBuilder()->build([
            'code' => $code,
            'type' => $type,
            'group' => AttributeGroupInterface::DEFAULT_CODE,
            'useable_as_grid_filter' => true,
        ], true);
        $this->getAttributeSaver()->save($attribute);
    }

    private function getAttributeSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.attribute');
    }

    private function getAttributeBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.base.attribute.builder');
    }

    private function logIn(string $username): void
    {
        $this->getAuthenticatorHelper()->logIn($username, $this->client);

        $this->getCacheClearer()->clear();
        $this->webClientHelper = $this->getWebClientHelper();
    }

    private function getRoleSaver(): BulkSaverInterface
    {
        return $this->get('pim_user.saver.role_with_permissions');
    }

    public function getRoleRepository(): IdentifiableObjectRepositoryInterface
    {
        return $this->get('pim_user.repository.role_with_permissions');
    }

    public function getAuthenticatorHelper(): AuthenticatorHelper
    {
        return $this->get('akeneo_integration_tests.helper.authenticator');
    }

    public function getCacheClearer(): EntityManagerClearerInterface
    {
        return $this->get('pim_connector.doctrine.cache_clearer');
    }

    public function getWebClientHelper(): WebClientHelper
    {
        return $this->get('akeneo_integration_tests.helper.web_client');
    }
}
