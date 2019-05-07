<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateVariantProductEndToEnd extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProductModel(
            [
                'code' => 'test',
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
                'code' => 'amor',
                'parent' => 'test',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ]
        );

        $this->createVariantProduct('simple', [
            'parent' => 'amor',
            'values'  => [
                'a_yes_no' => [
                    ['locale' => null, 'scope' => null, 'data' => false],
                ],
            ],
        ]);
    }

    public function testProductVariantCreationWithFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_family",
        "family": "familyA",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_variant_creation_family',
            'family'        => 'familyA',
            'parent'        => 'amor',
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_creation_family'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                "a_price" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "50.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
                "a_number_float" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "12.5000",
                    ],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => true,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_family');
    }

    public function testProductVariantCreationWithFamilyNotSpecifiedInSentData()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_family",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_variant_creation_family',
            'family'        => 'familyA',
            'parent'        => 'amor',
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_creation_family'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                "a_price" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "50.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
                "a_number_float" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "12.5000",
                    ],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => true,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_family');
    }

    public function testProductVariantCreationWithFamilySetToNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_family",
        "parent": "amor",
        "family": null,
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The family can\'t be "null" because your product with the identifier "product_variant_creation_family" is a variant product.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantCreationWithFamilyDifferentThanProductModelFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_family",
        "parent": "amor",
        "family": "familyA2",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The variant product family must be the same than its parent',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateProductVariantWithNonExistentProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_family",
        "parent": "invalid",
        "family": "familyA2",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "parent" expects a valid parent code. The parent product model does not exist, "invalid" given. Check the expected format on the API documentation.',
            '_links'  => ["documentation" => ["href" => "http://api.akeneo.com/api-reference.html#post_products"]]
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateProductVariantWithNoValuesInTheAxeAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation",
        "parent": "amor",
        "family": "familyA",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": null
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'attribute',
                    'message'  => 'Attribute "a_yes_no" cannot be empty, as it is defined as an axis for this entity',
                ],
            ],
        ];
        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateProductVariantWithNoAxeAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_with_missing_axe",
        "parent": "amor",
        "family": "familyA",
        "values": {}
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'attribute',
                    'message'  => 'Attribute "a_yes_no" cannot be empty, as it is defined as an axis for this entity',
                ],
            ],
        ];
        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantCreationWithGroups()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_groups",
        "groups": ["groupA", "groupB"],
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_variant_creation_groups',
            'family'        => "familyA",
            'parent'        => 'amor',
            'groups'        => ["groupA", "groupB"],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_creation_groups'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                "a_price" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "50.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
                "a_number_float" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "12.5000",
                    ],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => true,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_groups');
    }

    public function testProductVariantCreationWithCategories()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_categories",
        "parent": "amor",
        "categories": ["master", "categoryA"],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_variant_creation_categories',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => ["categoryA", "master"],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_creation_categories'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                "a_price" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "50.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
                "a_number_float" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "12.5000",
                    ],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => true,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_categories');
    }

    public function testProductVariantCreationWithAssociations()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "identifier": "product_variant_creation_associations",
    "parent": "amor",
    "values": {
        "a_yes_no": [
            {
                "locale": null,
                "scope": null,
                "data": true
            }
        ]
    },
    "associations": {
        "UPSELL": {
            "product_models": ["amor"]
        },
        "X_SELL": {
            "groups": ["groupA"],
            "products": ["simple"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_variant_creation_associations',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_creation_associations'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                "a_price" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "50.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
                "a_number_float" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "12.5000",
                    ],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => true,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                "PACK"         => [
                    "groups"   => [],
                    "products" => [],
                    "product_models" => [],
                ],
                "SUBSTITUTION" => [
                    "groups"   => [],
                    "products" => [],
                    "product_models" => [],
                ],
                "UPSELL"       => [
                    "groups"   => [],
                    "products" => [],
                    "product_models" => ["amor"],
                ],
                "X_SELL"       => [
                    "groups"   => ["groupA"],
                    "products" => ["simple"],
                    "product_models" => [],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_associations');
    }

    public function testProductVariantCreationWithProductValues()
    {
        $client = $this->createAuthenticatedClient();

        $files = [
            'akeneo_pdf' => $this->getFixturePath('akeneo.pdf'),
            'akeneo_jpg' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
            'ziggy_png'  => $this->getFileInfoKey($this->getFixturePath('ziggy.png')),
        ];

        $data =
<<<JSON
    {
        "identifier": "product_variant_creation_product_values",
        "groups": ["groupA", "groupB"],
        "parent": "amor",
        "family": "familyA",
        "categories": ["master", "categoryA"],
        "values": {
            "a_file": [{
                "locale": null,
                "scope": null,
                "data": "${files['akeneo_pdf']}"
            }],
            "an_image": [{
                "locale": null,
                "scope": null,
                "data": "${files['ziggy_png']}"
            }],
            "a_date": [{
                "locale": null,
                "scope": null,
                "data": "2016-06-13T00:00:00+02:00"
            }],
            "a_metric": [{
                "locale": null,
                "scope": null,
                "data": {
                    "amount": "987654321987.1234",
                    "unit": "KILOWATT"
                }
            }],
            "a_metric_without_decimal": [{
                "locale": null,
                "scope": null,
                "data": {
                    "amount": 98,
                    "unit": "CENTIMETER"
                }
            }],
            "a_metric_without_decimal_negative": [{
                "locale": null,
                "scope": null,
                "data": {
                    "amount": -20,
                    "unit": "CELSIUS"
                }
            }],
            "a_metric_negative": [{
                "locale": null,
                "scope": null,
                "data": {
                    "amount": "-20.5000",
                    "unit": "CELSIUS"
                }
            }],
            "a_multi_select": [{
                "locale": null,
                "scope": null,
                "data": ["optionA", "optionB"]
            }],
            "a_number_float": [{
                "locale": null,
                "scope": null,
                "data": "12.5678"
            }],
            "a_number_float_negative": [{
                "locale": null,
                "scope": null,
                "data": "-99.8732"
            }],
            "a_number_integer": [{
                "locale": null,
                "scope": null,
                "data": 42
            }],
            "a_number_integer_negative": [{
                "locale": null,
                "scope": null,
                "data": -42
            }],
            "a_price": [{
                "locale": null,
                "scope": null,
                "data": [{
                    "amount": "56.53",
                    "currency": "EUR"
                },
                {
                    "amount": "45.00",
                    "currency": "USD"
                }]
            }],
            "a_price_without_decimal": [{
                "locale": null,
                "scope": null,
                "data": [{
                    "amount": 56,
                    "currency": "EUR"
                },
                {
                    "amount": -45,
                    "currency": "USD"
                }]
            }],
            "a_ref_data_multi_select": [{
                "locale": null,
                "scope": null,
                "data": ["airguard", "braid"]
            }],
            "a_ref_data_simple_select": [{
                "locale": null,
                "scope": null,
                "data": "bright-lilac"
            }],
            "a_simple_select": [{
                "locale": null,
                "scope": null,
                "data": "optionA"
            }],
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "A name"
            }],
            "a_text_area": [{
                "locale": null,
                "scope": null,
                "data": "this is a very very very very very long  text"
            }],
            "a_yes_no": [{
                "locale": null,
                "scope": null,
                "data": false
            }],
            "a_localizable_scopable_image": [{
                "locale": "en_US",
                "scope": "ecommerce",
                "data": "${files['ziggy_png']}"
            }, {
                "locale": "fr_FR",
                "scope": "tablet",
                "data": "${files['akeneo_jpg']}"
            }],
            "a_scopable_price": [{
                "locale": null,
                "scope": "ecommerce",
                "data": [{
                    "amount": "15.00",
                    "currency": "EUR"
                }, {
                    "amount": "20.00",
                    "currency": "USD"
                }]
            }, {
                "locale": null,
                "scope": "tablet",
                "data": [{
                    "amount": "17.00",
                    "currency": "EUR"
                }, {
                    "amount": "24.00",
                    "currency": "USD"
                }]
            }],
            "a_localized_and_scopable_text_area": [{
                "locale": "en_US",
                "scope": "ecommerce",
                "data": "a text area for ecommerce in English"
            }, {
                "locale": "en_US",
                "scope": "tablet",
                "data": "a text area for tablets in English"
            }, {
                "locale": "fr_FR",
                "scope": "tablet",
                "data": "une zone de texte pour les tablettes en fran\u00e7ais"
            }]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_variant_creation_product_values',
            'family'        => 'familyA',
            'parent'        => "amor",
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_creation_product_values'],
                ],
                'a_number_float' => [
                    ['locale' => null, 'scope' => null, 'data' => '12.5000'],
                ],
                'a_price' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '50.00', 'currency' => 'EUR'],
                        ],
                    ],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_yes_no'                           => [
                    ['locale' => null, 'scope' => null, 'data' => false],
                ],
                'a_text_area'                           => [
                    ['locale' => null, 'scope' => null, 'data' => 'this is a very very very very very long  text'],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => []
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_product_values');
    }

    public function testProductVariantCreationWithIgnoredProperties()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "foo",
        "parent": "amor",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ],
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        },
        "created": "2014-06-14T13:12:50+02:00",
        "updated": "2014-06-14T13:12:50+02:00"
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'foo',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'foo'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                "a_price" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "50.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "a_localized_and_scopable_text_area" => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "my pink tshirt",
                    ],
                ],
                "a_number_float" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "12.5000",
                    ],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => true,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSameProducts($expectedProduct, 'foo');
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['created']);
        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['updated']);
    }

    public function testProductVariantCreationWithSameIdentifier()
    {
        $this->createVariantProduct('apollon_option_b_true', [
            'categories' => ['master'],
            'parent' => 'amor',
            'values' => [
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => false,
                    ],
                ],
            ],
        ]);

        $client = $this->createAuthenticatedClient();

        $product =
<<<JSON
    {
        "identifier": "apollon_option_b_true",
        "parent": "amor",
        "categories": ["master"],
        "values": {
            "a_yes_no": [
              {
                "locale": null,
                "scope": null,
                "data": false
              }
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $product);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'The same identifier is already set on another product',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenProductVariantRootModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "new_product_variant",
        "parent": "test",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ],
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'parent',
                    'message'  => 'The variant product "new_product_variant" cannot have product model "test" as parent, (this product model can only have other product models as children)',
                ],
                [
                    'property' => 'attribute',
                    'message' => 'Cannot set the property "sku" to this entity as it is not in the attribute set'
                ]
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
