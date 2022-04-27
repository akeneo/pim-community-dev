<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\ListProducts;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class SuccessListVariantProductDeltaEndToEnd extends AbstractProductTestCase
{
    public function testListVariantProductDeltaWhenUpdatingProductModel()
    {
        $discriminantDatetime = $this->getDiscriminantDatetime();
        $clientPatch = $this->createAuthenticatedClient();
        $updates =
<<<JSON
{
    "values": {
        "a_text": [
            {
                "data":"Awesome text.",
                "locale":null,
                "scope":null
            }
        ]
    }
}
JSON;
        $clientPatch->request('PATCH', 'api/rest/v1/product-models/prod_mod_optB', [], [], [], $updates);
        $this->assertSame(Response::HTTP_NO_CONTENT, $clientPatch->getResponse()->getStatusCode());

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $client = $this->createAuthenticatedClient();
        $search = sprintf('{"updated":[{"operator":">","value":"%s"}]}', $discriminantDatetime->format('Y-m-d H:i:s'));
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/products?search=' . $search);
        $response = $client->getResponse();

        $expected = <<<JSON
{
    "_links":{
        "self":{
            "href":"http:\/\/localhost\/api\/rest\/v1\/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"
        },
        "first":{
            "href":"http:\/\/localhost\/api\/rest\/v1\/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"
        }
    },
    "current_page":1,
    "_embedded":{
        "items":[
            {
                "_links":{
                    "self":{
                        "href":"http:\/\/localhost\/api\/rest\/v1\/products\/apollon_optionb_false"
                    }
                },
                "identifier":"apollon_optionb_false",
                "family":"familyA",
                "parent":"prod_mod_optB",
                "groups":[

                ],
                "categories":[
                    "master"
                ],
                "enabled":true,
                "values":{
                    "a_yes_no":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":false
                        }
                    ],
                    "a_text":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":"Awesome text."
                        }
                    ],
                    "a_simple_select":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":"optionB"
                        }
                    ],
                    "a_price":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":[
                                {
                                    "amount":"50.00",
                                    "currency":"EUR"
                                }
                            ]
                        }
                    ],
                    "a_number_float":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":"12.5000"
                        }
                    ],
                    "a_localized_and_scopable_text_area":[
                        {
                            "locale":"en_US",
                            "scope":"ecommerce",
                            "data":"my pink tshirt"
                        }
                    ]
                },
                "created":"2018-01-11T14:49:20+01:00",
                "updated":"2018-01-11T14:49:20+01:00",
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
                "_links":{
                    "self":{
                        "href":"http:\/\/localhost\/api\/rest\/v1\/products\/apollon_optionb_true"
                    }
                },
                "identifier":"apollon_optionb_true",
                "family":"familyA",
                "parent":"prod_mod_optB",
                "groups":[

                ],
                "categories":[
                    "master"
                ],
                "enabled":true,
                "values":{
                    "a_yes_no":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":true
                        }
                    ],
                    "a_text":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":"Awesome text."
                        }
                    ],
                    "a_simple_select":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":"optionB"
                        }
                    ],
                    "a_price":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":[
                                {
                                    "amount":"50.00",
                                    "currency":"EUR"
                                }
                            ]
                        }
                    ],
                    "a_number_float":[
                        {
                            "locale":null,
                            "scope":null,
                            "data":"12.5000"
                        }
                    ],
                    "a_localized_and_scopable_text_area":[
                        {
                            "locale":"en_US",
                            "scope":"ecommerce",
                            "data":"my pink tshirt"
                        }
                    ]
                },
                "created":"2018-01-11T14:49:20+01:00",
                "updated":"2018-01-11T14:49:20+01:00",
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
        $this->assertListResponse($response, $expected);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createProduct('product1', [
            new SetCategories(['master']),
            new SetMeasurementValue('a_metric', null, null, 10, 'KILOWATT'),
            new SetTextValue('a_text', null, null, 'Text')
        ]);
        $this->createProduct('product2', [
            new SetCategories(['categoryB']),
            // TODO : use SetImageValue when ready
            /**'values'     => [
                'a_localizable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'zh_CN', 'scope' => null]
                ]
            ]*/
        ]);
        $this->createProductModel(
            [
                'code' => 'parent_prod_mod',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_price'  => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area'  => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'prod_mod_optB',
                'parent' => 'parent_prod_mod',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ]
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'prod_mod_optA',
                'parent' => 'parent_prod_mod',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ]
                ]
            ]
        );

        $this->createVariantProduct('apollon_optionb_false', [
            new SetCategories(['master']),
            new ChangeParent('prod_mod_optB'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

        $this->createVariantProduct('apollon_optionb_true', [
            new SetCategories(['master']),
            new ChangeParent('prod_mod_optB'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);

        $this->createVariantProduct('apollon_optiona_true', [
            new SetCategories(['master']),
            new ChangeParent('prod_mod_optA'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
    }

    private function getDiscriminantDatetime(): \Datetime
    {
        sleep(1);
        $datetime = new \DateTime('now');
        sleep(1);

        return $datetime;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
