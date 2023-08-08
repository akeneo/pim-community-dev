<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\PublishedProduct;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;

class ListPublishedProductWithCompletenessEndToEnd extends AbstractPublishedProductTestCase
{
    /** @var Collection */
    private $products;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // product complete, whatever the scope
        $product1 = $this->createProduct('product_complete', [
            new SetFamily('familyA2'),
            new SetCategories(['categoryA', 'categoryB', 'master']),
            new SetMeasurementValue('a_metric', null, null, 1, 'WATT'),
            new SetNumberValue('a_number_float', null, null, '12.05'),
        ]);

        // product complete only on en_US-tablet & en-US-ecommerce
        $product2 = $this->createProduct('product_complete_en_locale', [
            new SetFamily('familyA1'),
            new SetCategories(['categoryA', 'master', 'master_china']),
            new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetDateValue('a_date', null, null, new \DateTime('2016-06-28')),
            new SetFileValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))),
        ]);

        // product incomplete
        $product3 = $this->createProduct('product_incomplete', [
            new SetFamily('familyA'),
            new SetCategories(['categoryA', 'master', 'master_china']),
            new SetFileValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))),
        ]);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
        $this->publishProduct($product1);
        $this->publishProduct($product2);
        $this->publishProduct($product3);

    }

    public function testPaginationWithCompletenessFilter()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"=","value":100,"scope":"ecommerce"}]}';
        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce&locales=en_US&limit=2&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=2&scope=ecommerce&locales=en_US&search=${searchEncoded}"},
        "first": {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=2&scope=ecommerce&locales=en_US&search=${searchEncoded}"},
        "next": {"href": "http://localhost/api/rest/v1/published-products?page=2&with_count=false&pagination_type=page&limit=2&scope=ecommerce&locales=en_US&search=${searchEncoded}"}
    },
    "_embedded"    : {
		"items": [
		    {
		        "_links":{
		            "self":{
		                "href": "http:\/\/localhost\/api\/rest\/v1\/published-products\/product_complete"
		            }
		        },
		        "identifier": "product_complete",
		        "family": "familyA2",
		        "groups": [],
		        "categories": ["categoryA","categoryB","master"],
		        "enabled": true,
		        "values": {
		            "a_metric": [
		                {"locale": null, "scope": null, "data": {"amount": "1.0000", "unit":"WATT"}}
		            ],
		            "a_number_float": [
		                {"locale": null, "scope": null, "data": "12.0500"}
		            ]
		        },
		        "created": "2017-03-17T16:11:46+01:00",
		        "updated": "2017-03-17T16:11:46+01:00",
		        "associations": {},
		        "quantified_associations": {}
		    },
		    {
		        "_links": {
		            "self": {"href": "http:\/\/localhost\/api\/rest\/v1\/published-products\/product_complete_en_locale"}
		        },
		        "identifier": "product_complete_en_locale",
		        "family": "familyA1",
		        "groups": [],
		        "categories": ["categoryA","master","master_china"],
		        "enabled": true,
		        "values": {
		            "a_localizable_image":[
		                {
		                    "locale": "en_US",
		                    "scope": null,
		                    "data": "6\/c\/3\/d\/6c3d4fe7736d7c51ac75a089fe4b1ad0409270e2_akeneo.jpg",
		                    "_links": {
		                        "download": {
		                            "href": "http:\/\/localhost\/api\/rest\/v1\/media-files\/6\/c\/3\/d\/6c3d4fe7736d7c51ac75a089fe4b1ad0409270e2_akeneo.jpg\/download"
		                        }
		                    }
		                }
		            ],
                    "a_date": [
                        {"locale": null, "scope": null, "data": "2016-06-28T00:00:00+02:00"}
                    ],
                    "a_file":[
                        {
                            "locale": null,
                            "scope": null,
                            "data": "9\/7\/a\/9\/97a97e0c6ecf25e8620ad49e98dd8cbf951a963e_akeneo.txt",
                            "_links":{
                                "download": {
                                    "href": "http:\/\/localhost\/api\/rest\/v1\/media-files\/9\/7\/a\/9\/97a97e0c6ecf25e8620ad49e98dd8cbf951a963e_akeneo.txt\/download"
                                }
                            }
                        }
                    ]
		        },
                "created": "2017-03-17T16:11:46+01:00",
                "updated": "2017-03-17T16:11:46+01:00",
                "associations": [],
                "quantified_associations": {}
		    }
		]
    },
    "current_page": 1
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testMaxPageWithOffsetPaginationType()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/published-products?page=101&limit=100');

        $message = addslashes('You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead');
        $expected = <<<JSON
{
    "code":422,
    "message":"${message}",
    "_links":{
        "documentation":{
            "href": "http:\/\/api.akeneo.com\/documentation\/pagination.html#the-search-after-method"
        }
    }
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());
    }
}
