<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAllAppsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllAppsQueryIntegration extends TestCase
{
    private GetAllAppsQuery $query;
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private ConnectedAppLoader $connectedAppLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAllAppsQuery::class);
        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');

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

    public function test_it_returns_all_apps(): void
    {
        $result = $this->query->execute();

        $this->assertEquals(
            GetAllAppsResult::create(2, [
                App::fromWebMarketplaceValues([
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
                ]),
                App::fromWebMarketplaceValues([
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
                ]),
            ]),
            $result
        );
    }

    public function test_it_returns_all_apps_with_a_connected_one(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'b18561ff-378e-41a5-babb-ca0ec0af569a',
            'bar'
        );

        $result = $this->query->execute();

        $appWithConnectedApp = App::fromWebMarketplaceValues([
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
        ]);
        $appWithConnectedApp = $appWithConnectedApp->withConnectedStatus(true);

        $this->assertEquals(
            GetAllAppsResult::create(2, [
                App::fromWebMarketplaceValues([
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
                ]),
                $appWithConnectedApp,
            ]),
            $result
        );
    }
}
