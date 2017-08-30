<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;

/**
 * We want to test the API is capable of returning an ordered list of 100 items.
 * ie, twice the size of a cursor page
 */
class SuccessLargeAndOrderedListPublishedProductIntegration extends AbstractPublishedProductTestCase
{
    /** @var PublishedProductInterface[] */
    private $publishedProducts;

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
            $publishedProduct = $this->publishProduct($product);

            $this->publishedProducts[$publishedProduct->getId()] = $publishedProduct;
        }
        // the API will return products sorted alphabetical by MySQL ID, and that's what we expect
        // for instance, if we have 100 products
        // 1, 10, 100, 11, 12, 13, 14, 15, 16, 17, 18, 19, 2, 20, 21...
        ksort($this->publishedProducts, SORT_STRING);
    }

    public function testPaginationAllPublishedProducts()
    {
        $standardizedPublishedProducts = [];
        foreach ($this->publishedProducts as $publishedProduct) {
            $standardizedPublishedProducts[] = $this->getStandardizedPublishedProduct($publishedProduct->getIdentifier());
        }
        $standardizedPublishedProducts = implode(',', $standardizedPublishedProducts);
        $lastEncryptedId = rawurlencode($this->getEncryptedId(end($this->publishedProducts)->getIdentifier()));

        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?limit=100&pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=100"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=100"},
        "next" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=100&search_after={$lastEncryptedId}"}
    },
    "_embedded"    : {
		"items": [
            {$standardizedPublishedProducts}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getStandardizedPublishedProduct($identifier)
    {
        $standardized = <<<JSON

{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/{$identifier}"
        }
    },
    "identifier": "{$identifier}",
    "family": null,
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
     * We want to test the API is capable of returning a list of 100 items.
     * (Twice the page of the cursor).
     *
     * @return int
     */
    private function getListSize()
    {
        $cursorPageSize = (int) $this->getParameter('pim_catalog.factory.product_cursor.page_size');

        return $cursorPageSize * 2;
    }
}
