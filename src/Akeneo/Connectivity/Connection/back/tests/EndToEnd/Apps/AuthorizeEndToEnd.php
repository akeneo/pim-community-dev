<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
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
    private FakeFeatureFlag $featureFlagMarketplaceActivate;
    private ClientProvider $clientProvider;
    private SessionInterface $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webMarketplaceApi = $this->get('akeneo_connectivity.connection.marketplace.web_marketplace_api');
        $this->featureFlagMarketplaceActivate = $this->get('akeneo_connectivity.connection.marketplace_activate.feature');
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
        $this->session = $this->get('session');
        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_is_redirected_to_the_error_when_authorizing_an_app_with_invalid_parameters(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/connect/apps/v1/authorize'
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        assert($response instanceof RedirectResponse);
        Assert::assertEquals('/#/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.constraint.client_id.not_blank', $response->getTargetUrl());
    }

    public function test_it_is_redirected_to_the_wizard_when_authorizing_an_app(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
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
                'redirect_uri' => 'http://shopware.example.com/callback',
                'state' => 'foo',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        assert($response instanceof RedirectResponse);
        Assert::assertEquals('/#/connect/apps/authorize?client_id=90741597-54c5-48a1-98da-a68e7ee0a715', $response->getTargetUrl());

        $authorizationInSession = $this->session->get('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715');
        Assert::assertNotEmpty($authorizationInSession);
        Assert::assertEquals([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'scope' => '',
            'redirect_uri' => 'http://shopware.example.com/callback',
            'state' => 'foo',
        ], json_decode($authorizationInSession, true));
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
