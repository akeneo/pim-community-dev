<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceApiSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith();
        $this->setFixturePath(__DIR__.'/fixtures/');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebMarketplaceApi::class);
        $this->shouldImplement(WebMarketplaceApiInterface::class);
    }

    public function it_returns_extensions(): void
    {
        $expectedItems = [
            [
                "id" => "90741597-54c5-48a1-98da-a68e7ee0a715",
                "name" => "Akeneo Shopware 6 Connector by EIKONA Media",
                "logo" => "https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N",
                "author" => "EIKONA Media GmbH",
                "partner" => "Akeneo Preferred Partner",
                "description" => "description_1",
                "url" => "url_1",
                "categories" => [
                    "E-commerce",
                ],
                "certified" => false,
            ],
            [
                "id" => "b18561ff-378e-41a5-babb-ca0ec0af569a",
                "name" => "Akeneo PIM Connector for Shopify",
                "logo" => "https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC",
                "author" => "StrikeTru",
                "partner" => "Akeneo Partner",
                "description" => "description_2",
                "url" => "url_2",
                "categories" => [
                    "E-commerce",
                ],
                "certified" => false,
            ],
        ];

        $extensions = ($this->getExtensions('CE', '5.0'))->getWrappedObject();

        Assert::assertCount(4, $extensions);

        Assert::assertArrayHasKey('total', $extensions);
        Assert::assertEquals(2, $extensions['total']);

        Assert::assertArrayHasKey('offset', $extensions);
        Assert::assertEquals(0, $extensions['offset']);

        Assert::assertArrayHasKey('limit', $extensions);
        Assert::assertEquals(100, $extensions['limit']);

        Assert::assertArrayHasKey('items', $extensions);
        Assert::assertCount(2, $extensions['items']);
        Assert::assertEquals($expectedItems[0], $extensions['items'][0]);
        Assert::assertEquals($expectedItems[1], $extensions['items'][1]);
    }
}
