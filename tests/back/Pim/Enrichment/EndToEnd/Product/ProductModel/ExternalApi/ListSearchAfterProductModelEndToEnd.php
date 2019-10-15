<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;

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
        $this->assertLink($responseBody, 'next', 'tshirt');
        $this->assertItems($responseBody, ['sweat', 'shoes', 'tshirt']);

        $this->followLink($client, 'next');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', 'tshirt');
        $this->assertLink($responseBody, 'next', 'handbag');
        $this->assertItems($responseBody, ['trousers', 'hat', 'handbag']);

        $this->followLink($client, 'next');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', 'handbag');
        $this->assertLinkDoesNotExist($responseBody, 'next');
        $this->assertItems($responseBody, []);

        $this->followLink($client, 'first');
        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertLink($responseBody, 'first', null);
        $this->assertLink($responseBody, 'self', null);
        $this->assertLink($responseBody, 'next', 'tshirt');
        $this->assertItems($responseBody, ['sweat', 'shoes', 'tshirt']);
    }

    private function followLink(Client $client, string $type): void
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
            Assert::assertSame($this->getEncryptedId($searchAfter), $output['search_after']);
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

    /**
     * @param string $productModelIdentifier

     * @return string
     */
    private function getEncryptedId($productModelIdentifier)
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');

        $productModelId = $this->get('database_connection')->fetchColumn(
            'SELECT id from pim_catalog_product_model where code = :code',
            [
                'code' => $productModelIdentifier,
            ]
        );

        return $encrypter->encrypt($productModelId);
    }
}
