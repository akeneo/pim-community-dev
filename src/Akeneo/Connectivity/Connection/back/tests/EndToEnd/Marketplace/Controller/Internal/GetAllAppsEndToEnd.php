<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllAppsEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $marketplaceActivateFeatureFlag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marketplaceActivateFeatureFlag = $this->get('akeneo_connectivity.connection.marketplace_activate.feature');
        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @group ce
     */
    public function test_it_gets_all_the_apps_with_certified_first(): void
    {
        $expectedApps = [
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM Connector for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru that seamlessly connects Akeneo PIM to the Shopify platform. It allows Shopify users to quickly setup a link to Akeneo PIM and sync all product catalog data to Shopify within minutes. It eliminates a lot of manual and repetitive work involved in updating the product catalog of a Shopify store. You can send and receive products, variations, modifiers, categories, standard and custom attributes, images and more from Akeneo PIM into your Shopify store. Compatible with all Akeneo PIM editions – Community, Growth, Enterprise (On-Premise, Cloud Flexibility, and Cloud Serenity) and StrikeTru\'s smallPIM.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-pim-connector-shopify?utm_medium=pim&utm_content=extension_link&utm_source=http%3A%2F%2Flocalhost%3A8080',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => true,
                'activate_url' => 'http://shopify.example.com/activate?pim_url=http%3A%2F%2Flocalhost%3A8080',
                'callback_url' => 'http://shopify.example.com/callback',
                'connected' => false,
                'isPending' => false,
                'isCustomApp' => false,
            ],
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media?utm_medium=pim&utm_content=extension_link&utm_source=http%3A%2F%2Flocalhost%3A8080',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate?pim_url=http%3A%2F%2Flocalhost%3A8080',
                'callback_url' => 'http://shopware.example.com/callback',
                'connected' => false,
                'isPending' => false,
                'isCustomApp' => false,
            ],
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            '/rest/marketplace/apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals([
            'total' => 2,
            'apps' => $expectedApps,
        ], $result);
    }

    /**
     * @group ce
     */
    public function test_it_receive_an_empty_response_when_the_feature_flag_is_disabled(): void
    {
        $this->marketplaceActivateFeatureFlag->disable();

        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            '/rest/marketplace/apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals([
            'total' => 0,
            'apps' => [],
        ], $result);
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
                'certified' => true,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
        ];

        $this->getWebMarketplaceApi()->setApps($apps);
    }

    private function getWebMarketplaceApi(): FakeWebMarketplaceApi
    {
        return $this->get(WebMarketplaceApi::class);
    }
}
