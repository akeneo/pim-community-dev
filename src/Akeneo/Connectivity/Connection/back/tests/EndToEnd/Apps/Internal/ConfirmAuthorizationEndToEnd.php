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
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationEndToEnd extends WebTestCase
{
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private FilePersistedFeatureFlags $featureFlags;
    private ClientProvider $clientProvider;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->featureFlags = $this->get('feature_flags');
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        // feature is directly injected in some services instead of being used through the registry, hence its deactivation in both services
        $this->get('akeneo_connectivity.connection.marketplace_activate.feature')->disable();
        $this->featureFlags->disable('marketplace_activate');
        $this->authenticateAsAdmin();

        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';

        $this->client->request(
            'POST',
            \sprintf('/rest/apps/confirm-authorization/%s', $appId),
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();

        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';

        $this->client->request(
            'POST',
            \sprintf('/rest/apps/confirm-authorization/%s', $appId),
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        \assert($response instanceof RedirectResponse);
        Assert::assertEquals('/', $response->getTargetUrl());
    }

    public function test_it_returns_json_with_error_on_missing_id(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            '/rest/apps/confirm-authorization/unknown-id',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertArrayHasKey('errors', $content);
        Assert::assertGreaterThan(0, \is_countable($content['errors']) ? \count($content['errors']) : -1);
    }

    public function test_it_returns_json_with_app_id(): void
    {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';

        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp($appId));
        $this->clientProvider->findOrCreateClient($app);
        $this->appAuthorizationHandler->handle(
            new RequestAppAuthorizationCommand(
                $appId,
                'code',
                'write_catalog_structure delete_products read_association_types',
                'http://anyurl.test'
            )
        );

        $this->client->request(
            'POST',
            \sprintf('/rest/apps/confirm-authorization/%s', $appId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertArrayHasKey('appId', $responseContent, 'Response missing appId');
        Assert::assertIsString($responseContent['appId']);
        Assert::assertArrayHasKey('userGroup', $responseContent, 'Response missing userGroup');
        Assert::assertIsString($responseContent['userGroup']);
        Assert::assertArrayHasKey('redirectUrl', $responseContent);
        Assert::assertIsString($responseContent['redirectUrl']);
    }

    public function test_it_returns_json_with_app_id_when_openid_scope(): void
    {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';

        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp($appId));
        $this->clientProvider->findOrCreateClient($app);
        $this->appAuthorizationHandler->handle(
            new RequestAppAuthorizationCommand(
                $appId,
                'code',
                'write_catalog_structure delete_products read_association_types openid',
                'http://anyurl.test'
            )
        );

        $this->client->request(
            'POST',
            \sprintf('/rest/apps/confirm-authorization/%s', $appId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertArrayHasKey('appId', $responseContent, 'Response missing appId');
        Assert::assertIsString($responseContent['appId']);
        Assert::assertArrayHasKey('userGroup', $responseContent, 'Response missing userGroup');
        Assert::assertIsString($responseContent['userGroup']);
        Assert::assertArrayHasKey('redirectUrl', $responseContent);
        Assert::assertIsString($responseContent['redirectUrl']);
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
