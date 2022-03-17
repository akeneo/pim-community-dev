<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAllAppsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllAppsQuerySpec extends ObjectBehavior
{
    private $items;

    public function let(
        WebMarketplaceApiInterface $webMarketplaceApi,
        Connection $connection
    ) {
        $this->items = [
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM App for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/shopify-app-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru that seamlessly connects Akeneo PIM to the Shopify platform. It allows Shopify users to quickly setup a link to Akeneo PIM and sync all product catalog data to Shopify within minutes. It eliminates a lot of manual and repetitive work involved in updating the product catalog of a Shopify store. You can send and receive products, variations, modifiers, categories, standard and custom attributes, images and more from Akeneo PIM into your Shopify store. Compatible with all Akeneo PIM editions – Community, Growth, Enterprise (On-Premise, Cloud Flexibility, and Cloud Serenity) and StrikeTru\'s smallPIM.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-pim-app-shopify',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569b',
                'name' => 'Akeneo PIM App for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/shopify-app-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru that seamlessly connects Akeneo PIM to the Shopify platform. It allows Shopify users to quickly setup a link to Akeneo PIM and sync all product catalog data to Shopify within minutes. It eliminates a lot of manual and repetitive work involved in updating the product catalog of a Shopify store. You can send and receive products, variations, modifiers, categories, standard and custom attributes, images and more from Akeneo PIM into your Shopify store. Compatible with all Akeneo PIM editions – Community, Growth, Enterprise (On-Premise, Cloud Flexibility, and Cloud Serenity) and StrikeTru\'s smallPIM.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-pim-app-shopify',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
        ];

        $webMarketplaceApi->getApps(0, 2)->willreturn([
            'total' => 3,
            'offset' => 0,
            'limit' => 2,
            'items' => [
                $this->items[0],
                $this->items[1],
            ],
        ]);

        $webMarketplaceApi->getApps(2, 2)->willreturn([
            'total' => 3,
            'offset' => 2,
            'limit' => 2,
            'items' => [
                $this->items[2],
            ],
        ]);

        $connection->fetchFirstColumn(Argument::any())->willReturn([]);

        $this->beConstructedWith($connection, $webMarketplaceApi, 2);
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(GetAllAppsQuery::class);
    }

    public function it_execute_and_returns_app_result()
    {
        $this->execute()->shouldBeLike(GetAllAppsResult::create(3, \array_map(function ($item) {
            return App::fromWebMarketplaceValues($item);
        }, $this->items)));
    }

    public function it_set_connected_to_true_on_connected_apps(Connection $connection)
    {
        $connection->fetchFirstColumn(Argument::any())->willReturn([
            $this->items[0]['id'],
            $this->items[2]['id'],
        ]);

        $this->items[0]['connected'] = true;
        $this->items[1]['connected'] = false;
        $this->items[2]['connected'] = true;

        $this->execute()->shouldBeLike(GetAllAppsResult::create(3, \array_map(function ($item) {
            return App::fromWebMarketplaceValues($item);
        }, $this->items)));
    }
}
