<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\InternalApi;

use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SwitchMainIdentifierControllerEndToEnd extends WebTestCase
{
    private const ROUTE = 'internal_api_attribute_switch_main_identifier';
    private const HEADERS = [
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
        'CONTENT_TYPE' => 'application/json',
    ];
    private WebClientHelper $webClientHelper;
    private KernelBrowser $client;

    public function test_it_should_redirect_on_non_xhr_request(): void
    {
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
        Assert::assertTrue($response->isRedirect('/'));
    }

    public function test_it_should_be_a_bad_request_without_param(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            self::HEADERS
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_it_is_a_success()
    {
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
        $fixturesLoader->load($catalogs->useMinimalCatalog());

        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    private function get(string $service)
    {
        return self::getContainer()->get($service);
    }
}
