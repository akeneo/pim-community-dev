<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\ListProducts;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Doctrine\Common\Collections\Collection;

/**
 * @group ce
 */
class ListProductWithCompletenessEndToEnd extends AbstractProductTestCase
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
        $this->createProduct('product_complete', [
            new SetFamily('familyA2'),
            new SetCategories(['categoryA', 'categoryB', 'master']),
            new SetMeasurementValue('a_metric', null, null, 1, 'WATT'),
            new SetNumberValue('a_number_float', null, null, '12.05')
        ]);

        // product complete only on en_US-tablet & en-US-ecommerce
        $this->createProduct('product_complete_en_locale', [
            new SetFamily('familyA1'),
            new SetCategories(['categoryA', 'master', 'master_china']),
            // TODO use SetImageValue when ready
            new SetTextValue(
                'a_localizable_image',
                null,
                null,
                $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))
            ),
            new SetDateValue('a_date', null, null, new \DateTime('2016-06-28')),
            // TODO: use SetFileValue when ready
            new SetTextValue(
                'a_file',
                null,
                null,
                $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))
            )
        ]);

        // product incomplete
        $this->createProduct('product_incomplete', [
            new SetFamily('familyA'),
            new SetCategories(['categoryA', 'master', 'master_china']),
            // TODO: use SetFileValue when ready
            new SetTextValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.txt')))
        ]);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    public function testPaginationWithCompletenessFilter()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"=","value":100,"scope":"ecommerce"}]}';
        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&locales=en_US&limit=2&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=2&search=${searchEncoded}&scope=ecommerce&locales=en_US"},
        "first": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=2&search=${searchEncoded}&scope=ecommerce&locales=en_US"},
        "next": {"href": "http://localhost/api/rest/v1/products?page=2&with_count=false&pagination_type=page&limit=2&search=${searchEncoded}&scope=ecommerce&locales=en_US"}
    },
    "current_page" : 1,
    "_embedded"    : {
		"items": [
		    {
		        "_links":{
		            "self":{
		                "href": "http:\/\/localhost\/api\/rest\/v1\/products\/product_complete"
		            }
		        },
		        "identifier": "product_complete",
		        "family": "familyA2",
		        "parent": null,
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
		        "associations": {
		            "PACK": {
		                "products": [],
		                "product_models": [],
		                "groups": []
		            },
		            "SUBSTITUTION": {
		                "products": [],
		                "product_models": [],
		                "groups": []
		            },
		            "UPSELL": {
		                "products": [],
		                "product_models": [],
		                "groups": []
		            },
		            "X_SELL": {
		                "products": [],
		                "product_models": [],
		                "groups": []
		            }
		        },
		        "quantified_associations": {}
		    },
		    {
		        "_links": {
		            "self": {"href": "http:\/\/localhost\/api\/rest\/v1\/products\/product_complete_en_locale"}
		        },
		        "identifier": "product_complete_en_locale",
		        "family": "familyA1",
                "parent": null,
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
                "associations": {
                    "PACK": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    },
                    "SUBSTITUTION": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    },
                    "UPSELL": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    },
                    "X_SELL": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    }
		        },
		        "quantified_associations": {}
		    }
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
        return $this->catalog->useTechnicalCatalog();
    }
}
