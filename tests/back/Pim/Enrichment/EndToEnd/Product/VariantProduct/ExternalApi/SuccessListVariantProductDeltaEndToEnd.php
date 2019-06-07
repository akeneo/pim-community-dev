<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

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

        $this->waitUntilJobsHaveBeenConsumed();

        $client = $this->createAuthenticatedClient();
        $search = sprintf('{"updated":[{"operator":">","value":"%s"}]}', $discriminantDatetime->format('Y-m-d H:i:s'));
        $searchEncoded = rawurlencode($search);

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
                "associations":{

                }
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
                "associations":{

                }
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
            'categories' => ['master'],
            'values'     => [
                'a_metric' => [
                    ['data' => ['amount' => 10, 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                ],
                'a_text' => [
                    ['data' => 'Text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->createProduct('product2', [
            'categories' => ['categoryB'],
            'values'     => [
                'a_localizable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'zh_CN', 'scope' => null]
                ]
            ]
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
            'categories' => ['master'],
            'parent' => 'prod_mod_optB',
            'values' => [
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => false,
                    ]
                ]
            ]
        ]);

        $this->createVariantProduct('apollon_optionb_true', [
            'categories' => ['master'],
            'parent' => 'prod_mod_optB',
            'values' => [
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => true,
                    ]
                ]
            ]
        ]);

        $this->createVariantProduct('apollon_optiona_true', [
            'categories' => ['master'],
            'parent' => 'prod_mod_optA',
            'values' => [
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => true,
                    ]
                ]
            ]
        ]);
    }

    private function waitUntilJobsHaveBeenConsumed(): void
    {
        $jobLauncher = new JobLauncher(static::$kernel);
        while ($jobLauncher->hasJobInQueue()) {
            $jobLauncher->launchConsumerOnce();
        }
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
