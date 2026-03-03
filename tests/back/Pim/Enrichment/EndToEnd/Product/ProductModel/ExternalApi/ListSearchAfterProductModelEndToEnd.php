<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ListSearchAfterProductModelEndToEnd extends AbstractProductModelTestCase
{
    /**
     * @group critical
     */
    public function test_navigation_links()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models?pagination_type=search_after&limit=3');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', null);
        $this->assertLink($responseBody, 'next', 'shoes');
        $this->assertItems($responseBody, ['handbag', 'hat', 'shoes']);

        $this->followLink($client, 'next');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', 'shoes');
        $this->assertLink($responseBody, 'next', 'tshirt');
        $this->assertItems($responseBody, ['sweat', 'trousers', 'tshirt']);

        $this->followLink($client, 'next');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', 'tshirt');
        $this->assertLinkDoesNotExist($responseBody, 'next');
        $this->assertItems($responseBody, []);

        $this->followLink($client, 'first');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', null);
        $this->assertLink($responseBody, 'next', 'shoes');
        $this->assertItems($responseBody, ['handbag', 'hat', 'shoes']);
    }

    private function followLink(KernelBrowser $client, string $type): void
    {
        $link = $this->getLink(json_decode($client->getResponse()->getContent(), true), $type);
        Assert::assertNotNull($link);

        $client->request('GET', urldecode($link));
    }

    private function assertLink(array $responseBody, string $type, ?string $searchAfter): void
    {
        $link = $this->getLink($responseBody, $type);
        Assert::assertNotNull($link);

        $output = [];
        parse_str($link, $output);

        Assert::assertArrayHasKey('pagination_type', $output);
        Assert::assertSame('search_after', $output['pagination_type']);

        if (null === $searchAfter) {
            Assert::assertArrayNotHasKey('search_after', $output);
        } else {
            Assert::assertArrayHasKey('search_after', $output);
            Assert::assertSame($searchAfter, $output['search_after']);
        }
    }

    private function assertLinkDoesNotExist(array $responseBody, string $type): void
    {
        Assert::assertNull($this->getLink($responseBody, $type));
    }

    private function assertItems(array $responseBody, array $productModelCodes): void
    {
        Assert::assertTrue(isset($responseBody['_embedded']['items']));
        $items = $responseBody['_embedded']['items'];
        Assert::assertSame(count($productModelCodes), count($items));
        Assert::assertSame($productModelCodes, array_map(function (array $item) {
            return $item['code'];
        }, $items));
    }

    private function getLink(array $responseBody, string $type): ?string
    {
        Assert::assertArrayHasKey('_links', $responseBody);

        if (!isset($responseBody['_links'][$type])) {
            return null;
        }

        return urldecode($responseBody['_links'][$type]['href']);
    }
}
