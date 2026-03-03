<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizeEndToEnd extends WebTestCase
{
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private FilePersistedFeatureFlags $featureFlags;
    private ClientProvider $clientProvider;
    private SessionInterface $session;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;
    private CreateConnectedAppWithAuthorizationHandler $createConnectedAppWithAuthorizationHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->featureFlags = $this->get('feature_flags');
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->session = $this->get('session');
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->createConnectedAppWithAuthorizationHandler = $this->get(CreateConnectedAppWithAuthorizationHandler::class);
        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_is_redirected_to_the_error_when_authorizing_an_app_with_invalid_client_id(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/connect/apps/v1/authorize',
            [
                'client_id' => 'unknown_client_id',
                'response_type' => 'code',
                'scope' => 'read_catalog_structure'
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        \assert($response instanceof RedirectResponse);
        Assert::assertEquals('/#/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found', $response->getTargetUrl());
    }

    public function test_it_is_redirected_to_the_wizard_without_unknown_scopes_when_authorizing_an_app(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp('90741597-54c5-48a1-98da-a68e7ee0a715'));
        $this->clientProvider->findOrCreateClient($app);
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/connect/apps/v1/authorize',
            [
                'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'response_type' => 'code',
                'state' => 'foo',
                'scope' => 'read_catalog_structure SOME_UNKNOWN_SCOPE write_categories openid profile'
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        \assert($response instanceof RedirectResponse);
        Assert::assertEquals('/#/connect/apps/authorize?client_id=90741597-54c5-48a1-98da-a68e7ee0a715', $response->getTargetUrl());

        $authorizationInSession = $this->session->get('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715');
        Assert::assertNotEmpty($authorizationInSession);
        Assert::assertEquals([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'authorization_scope' => 'read_catalog_structure write_categories',
            'authentication_scope' => 'openid profile',
            'redirect_uri' => 'http://shopware.example.com/callback',
            'state' => 'foo',
        ], \json_decode($authorizationInSession, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_it_is_redirected_to_the_app_when_already_authorized(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp('90741597-54c5-48a1-98da-a68e7ee0a715'));
        $this->clientProvider->findOrCreateClient($app);
        $this->appAuthorizationHandler->handle(new RequestAppAuthorizationCommand(
            '90741597-54c5-48a1-98da-a68e7ee0a715',
            'code',
            'write_catalog_structure delete_products read_association_types',
            'http://anyurl.test'
        ));
        $this->createConnectedAppWithAuthorizationHandler->handle(new CreateConnectedAppWithAuthorizationCommand(
            '90741597-54c5-48a1-98da-a68e7ee0a715'
        ));
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/connect/apps/v1/authorize',
            [
                'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'response_type' => 'code',
                'state' => 'foo',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        \assert($response instanceof RedirectResponse);
        Assert::matchesRegularExpression('^http:\/\/shopware\.example\.com\/callback\?code=[a-zA-Z0-9@=]+&state=foo$');
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
