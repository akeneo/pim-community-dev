<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;

/**
 * We want to test the API is capable of returning an ordered list of 100 items.
 * ie, twice the size of a cursor page
 */
class SuccessLargeAndOrderedListPublishedProductEndToEnd extends AbstractPublishedProductTestCase
{
    /** @var PublishedProductInterface[] */
    private $publishedProducts;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $identifiers = [];
        for ($i = 0; $i < $this->getListSize(); $i++) {
            $identifiers[] = 'sku-' . str_pad($i, 4, '0', STR_PAD_LEFT);
        }

        foreach ($identifiers as $identifier) {
            $product = $this->createProduct($identifier, []);
            $publishedProduct = $this->publishProduct($product);

            $this->publishedProducts[$publishedProduct->getIdentifier()] = $publishedProduct;
        }
        
        ksort($this->publishedProducts, SORT_STRING);
    }

    public function testPaginationAllPublishedProducts()
    {
        $standardizedPublishedProducts = [];
        foreach ($this->publishedProducts as $publishedProduct) {
            $standardizedPublishedProducts[] = $this->getStandardizedPublishedProduct($publishedProduct->getIdentifier());
        }
        $standardizedPublishedProducts = implode(',', $standardizedPublishedProducts);
        $lastProductIdentifier = end($this->publishedProducts)->getIdentifier();

        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?limit='.$this->getListSize().'&pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit={$this->getListSize()}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit={$this->getListSize()}"},
        "next" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit={$this->getListSize()}&search_after={$lastProductIdentifier}"}
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
    "associations": {},
    "quantified_associations": {}
}
JSON;

        return $standardized;
    }

    /**
     * We want to test the API is capable of returning a list of 100 items.
     *
     * @return int
     */
    private function getListSize(): int
    {
        return (int) $this->getParameter('api_input_max_resources_number');
    }
}
