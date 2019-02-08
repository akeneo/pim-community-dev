<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * We want to test the API is capable of returning an ordered list twice the size of a cursor page
 *
 * @group ce
 */
class SuccessLargeAndOrderedListProductIntegration extends AbstractProductTestCase
{
    /** @var ProductInterface[] */
    private $products;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $identifiers = [];
        for ($i = 0; $i < $this->getListSize(); $i++) {
            $identifiers[] = 'sku-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
        }

        foreach ($identifiers as $identifier) {
            $product = $this->createProduct($identifier, []);
            $this->products[$product->getId()] = $product;
        }
        // the API will return products sorted alphabetical by MySQL ID, and that's what we expect
        // 1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 2, 20, 21...
        ksort($this->products, SORT_STRING);
    }

    public function testPaginationAllProducts()
    {
        $standardizedProducts = [];
        foreach ($this->products as $product) {
            $standardizedProducts[] = $this->getStandardizedProduct($product->getIdentifier());
        }
        $standardizedProducts = implode(',', $standardizedProducts);
        $lastEncryptedId = rawurlencode($this->getEncryptedId(end($this->products)));

        $client = $this->createAuthenticatedClient();
        $client->request('GET', "api/rest/v1/products?limit={$this->getListSize()}&pagination_type=search_after");
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit={$this->getListSize()}"},
        "first" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit={$this->getListSize()}"},
        "next" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit={$this->getListSize()}&search_after={$lastEncryptedId}"}
    },
    "_embedded"    : {
		"items": [
            {$standardizedProducts}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getStandardizedProduct($identifier): string
    {
        $standardized = <<<JSON

{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/{$identifier}"
        }
    },
    "identifier": "{$identifier}",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": [],
    "enabled": true,
    "values": {},
    "created": "2017-05-12T16:46:14+02:00",
    "updated": "2017-05-12T16:46:14+02:00",
    "associations": {}
}
JSON;

        return $standardized;
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    private function getEncryptedId(ProductInterface $product): string
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');

        return $encrypter->encrypt($product->getId());
    }

    /**
     * @return int
     */
    private function getListSize(): int
    {
        return (int)$this->getParameter('api_input_max_resources_number');
    }
}
