<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAllExtensionsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllExtensionsQuerySpec extends ObjectBehavior
{
    private const PAGINATION = 2;

    public function let(
        WebMarketplaceApiInterface $webMarketplaceApi
    ): void {
        $this->beConstructedWith($webMarketplaceApi, self::PAGINATION);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GetAllExtensionsQuery::class);
    }

    public function it_execute_and_returns_extension_result(
        WebMarketplaceApiInterface $webMarketplaceApi
    ): void {
        $items = [
            [
                'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
                'name' => 'Shopify connector',
                'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
                'author' => 'Ideatarmac',
                'partner' => 'Akeneo Partner',
                'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
                'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector',
                'categories' => ['E-commerce'],
                'certified' => false,
            ],
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'description_1',
                'url' => 'url_1',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM Connector for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'description_2',
                'url' => 'url_2',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
            ],
        ];
        $webMarketplaceApi->getExtensions(0, 2)->willreturn([
            'total' => 3,
            'offset' => 0,
            'limit' => 2,
            'items' => [
                $items[0],
                $items[1],
            ],
        ]);
        $webMarketplaceApi->getExtensions(2, 2)->willreturn([
            'total' => 3,
            'offset' => 2,
            'limit' => 2,
            'items' => [
                $items[2],
            ],
        ]);

        $this->execute()->shouldBeLike(GetAllExtensionsResult::create(3, \array_map(fn ($item): \Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension => Extension::fromWebMarketplaceValues($item), $items)));
    }
}
