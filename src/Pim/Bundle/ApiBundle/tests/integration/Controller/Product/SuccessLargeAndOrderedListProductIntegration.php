<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * We want to test the API is capable of returning an ordered list of 100 items.
 * ie, twice the size of a cursor page
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
            $identifiers[] = 'sku-' . str_pad($i, 4, '0', STR_PAD_LEFT);
        }

        foreach ($identifiers as $identifier) {
            $product = $this->createProduct($identifier, []);
            $this->products[$product->getId()] = $product;
        }
        // the API will return products sorted alphabetical by MySQL ID, and that's what we expect
        // for instance, if we have 100 products
        // 1, 10, 100, 11, 12, 13, 14, 15, 16, 17, 18, 19, 2, 20, 21...
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
        $client->request('GET', 'api/rest/v1/products?limit=100&pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=100"},
        "first" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=100"},
        "next" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=100&search_after={$lastEncryptedId}"}
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
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getStandardizedProduct($identifier)
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
    "variant_group": null,
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
    private function getEncryptedId(ProductInterface $product)
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');

        return $encrypter->encrypt($product->getId());
    }

    /**
     * We want to test the API is capable of returning a list of 100 items.
     * (Twice the page of the cursor).
     *
     * @return int
     */
    private function getListSize()
    {
        $cursorPageSize = (int)$this->getParameter('pim_catalog.factory.product_cursor.page_size');

        return $cursorPageSize * 2;
    }
}
