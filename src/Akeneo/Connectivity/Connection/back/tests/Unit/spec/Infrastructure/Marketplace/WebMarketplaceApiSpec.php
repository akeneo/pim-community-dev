<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\StreamInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceApiSpec extends ObjectBehavior
{
    public function let(
        Client $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases
    ): void {
        $this->beConstructedWith($client, $webMarketplaceAliases);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebMarketplaceApi::class);
        $this->shouldImplement(WebMarketplaceApiInterface::class);
    }

    public function it_returns_extensions(
        Client $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        Response $response,
        StreamInterface $stream
    ): void {
        $expectedResponse = [
            'total' => 2,
            'limit' => 10,
            'offset' => 0,
            'items' => [
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
            ],
        ];

        $webMarketplaceAliases->getEdition()->willReturn('community-edition');
        $webMarketplaceAliases->getVersion()->willReturn('5.0');
        $stream->getContents()->willReturn(json_encode($expectedResponse));
        $response->getBody()->willReturn($stream);
        $client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'edition' => 'community-edition',
                'version' => '5.0',
                'offset' => 0,
                'limit' => 10,
            ],
        ])->willReturn($response);

        $extensions = ($this->getExtensions())->getWrappedObject();

        Assert::assertArrayHasKey('total', $extensions);
        Assert::assertEquals(2, $extensions['total']);

        Assert::assertArrayHasKey('offset', $extensions);
        Assert::assertEquals(0, $extensions['offset']);

        Assert::assertArrayHasKey('limit', $extensions);
        Assert::assertEquals(10, $extensions['limit']);

        Assert::assertArrayHasKey('items', $extensions);
        Assert::assertCount(2, $extensions['items']);
        Assert::assertEquals($expectedResponse['items'][0], $extensions['items'][0]);
        Assert::assertEquals($expectedResponse['items'][1], $extensions['items'][1]);
    }
}
