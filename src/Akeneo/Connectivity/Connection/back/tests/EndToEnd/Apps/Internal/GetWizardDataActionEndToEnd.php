<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetWizardDataActionEndToEnd extends WebTestCase
{
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private ClientProvider $clientProvider;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;

    public function test_to_get_wizard_data(): void
    {
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp('90741597-54c5-48a1-98da-a68e7ee0a715'));
        $this->clientProvider->findOrCreateClient($app);

        $command = new RequestAppAuthorizationCommand(
            '90741597-54c5-48a1-98da-a68e7ee0a715',
            'code',
            'write_catalog_structure delete_products read_association_types openid profile email',
            'http://anyurl.test'
        );
        $this->appAuthorizationHandler->handle($command);

        $this->client->request(
            'GET',
            '/rest/apps/load-wizard-data/90741597-54c5-48a1-98da-a68e7ee0a715',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals([
            'appName' => 'Akeneo Shopware 6 Connector by EIKONA Media',
            'appLogo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
            'appUrl' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
            'scopeMessages' => [
                [
                    'icon' => 'products',
                    'type' => 'delete',
                    'entities' => 'products',
                ],
                [
                    'icon' => 'association_types',
                    'type' => 'view',
                    'entities' => 'association_types',
                ],
                [
                    'icon' => 'catalog_structure',
                    'type' => 'edit',
                    'entities' => 'catalog_structure',
                ],
            ],
            'authenticationScopes' => ['email', 'profile'],
        ], \json_decode($response->getContent(), true));
    }

    public function test_it_throws_an_exception_when_app_not_found_into_session(): void
    {
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp('90741597-54c5-48a1-98da-a68e7ee0a715'));
        $this->clientProvider->findOrCreateClient($app);

        $this->client->request(
            'GET',
            '/rest/apps/load-wizard-data/90741597-54c5-48a1-98da-a68e7ee0a715',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_throws_an_exception_when_app_not_found_into_database(): void
    {
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/rest/apps/load-wizard-data/not_an_existing_app',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_authentication_scopes_are_empty(): void
    {
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp('90741597-54c5-48a1-98da-a68e7ee0a715'));
        $this->clientProvider->findOrCreateClient($app);

        $command = new RequestAppAuthorizationCommand(
            '90741597-54c5-48a1-98da-a68e7ee0a715',
            'code',
            'write_catalog_structure delete_products read_association_types',
            'http://anyurl.test'
        );
        $this->appAuthorizationHandler->handle($command);

        $this->client->request(
            'GET',
            '/rest/apps/load-wizard-data/90741597-54c5-48a1-98da-a68e7ee0a715',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals([
            'appName' => 'Akeneo Shopware 6 Connector by EIKONA Media',
            'appLogo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
            'appUrl' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
            'scopeMessages' => [
                [
                    'icon' => 'products',
                    'type' => 'delete',
                    'entities' => 'products',
                ],
                [
                    'icon' => 'association_types',
                    'type' => 'view',
                    'entities' => 'association_types',
                ],
                [
                    'icon' => 'catalog_structure',
                    'type' => 'edit',
                    'entities' => 'catalog_structure',
                ],
            ],
            'authenticationScopes' => [],
        ], \json_decode($response->getContent(), true));
    }

    public function test_authorization_scopes_are_empty(): void
    {
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp('90741597-54c5-48a1-98da-a68e7ee0a715'));
        $this->clientProvider->findOrCreateClient($app);

        $command = new RequestAppAuthorizationCommand(
            '90741597-54c5-48a1-98da-a68e7ee0a715',
            'code',
            '',
            'http://anyurl.test'
        );
        $this->appAuthorizationHandler->handle($command);

        $this->client->request(
            'GET',
            '/rest/apps/load-wizard-data/90741597-54c5-48a1-98da-a68e7ee0a715',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals([
            'appName' => 'Akeneo Shopware 6 Connector by EIKONA Media',
            'appLogo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
            'appUrl' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
            'scopeMessages' => [],
            'authenticationScopes' => [],
        ], \json_decode($response->getContent(), true));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->get('akeneo_connectivity.connection.marketplace_activate.feature')->enable();
        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadAppsFixtures(): void
    {
        $apps = [
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM Connector for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru that seamlessly connects Akeneo PIM to the Shopify platform. It allows Shopify users to quickly setup a link to Akeneo PIM and sync all product catalog data to Shopify within minutes. It eliminates a lot of manual and repetitive work involved in updating the product catalog of a Shopify store. You can send and receive products, variations, modifiers, categories, standard and custom attributes, images and more from Akeneo PIM into your Shopify store. Compatible with all Akeneo PIM editions – Community, Growth, Enterprise (On-Premise, Cloud Flexibility, and Cloud Serenity) and StrikeTru\'s smallPIM.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-pim-connector-shopify',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
        ];

        $this->webMarketplaceApi->setApps($apps);
    }
}
