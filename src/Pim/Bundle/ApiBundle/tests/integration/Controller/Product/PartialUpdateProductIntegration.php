<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateProductIntegration extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createProduct('product_categories', [
            'categories' => ['master'],
        ]);

        $this->createProduct('product_categories', [
            'categories' => ['master'],
        ]);

        $this->createProduct('product_family', [
            'family' => 'familyA2',
        ]);

        $this->createProduct('product_groups', [
            'groups' => ['groupA'],
        ]);

        $this->createProduct('product_variant_group', [
            'variant_group' => 'variantA',
            'values'        => [
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
            ],
        ]);

        $this->createProduct('product_associations', [
            'associations'  => [
                "PACK"         => ["groups"   => [], "products" => []],
                "SUBSTITUTION" => ["groups"   => [], "products" => []],
                "UPSELL"       => ["groups"   => [], "products" => []],
                "X_SELL"       => ["groups"   => ["groupA"], "products" => ["product_categories"]],
            ],
        ]);


        $this->createProduct('complete', [
            'family'        => 'familyA2',
            'groups'        => ['groupA'],
            'variant_group' => 'variantA',
            'categories'    => ['master'],
            'values'        => [
                'a_metric' => [
                    ['data' => ['amount' => '10.0000', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null],
                ],
                'a_date'   => [
                    ['data' => '2016-06-13T00:00:00+02:00', 'locale' => null, 'scope' => null],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
            ],
            'associations'  => [
                "PACK"         => ["groups"   => [], "products" => []],
                "SUBSTITUTION" => ["groups"   => [], "products" => []],
                "UPSELL"       => ["groups"   => [], "products" => []],
                "X_SELL"       => ["groups"   => ["groupA"], "products" => ["product_categories"]],
            ],
        ]);

        // localizable, categorized in 1 tree (master)
        $this->createProduct('localizable', [
            'categories' => ['categoryB'],
            'values'     => [
                'a_localizable_image' => [
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'zh_CN', 'scope' => null]
                ]
            ]
        ]);

        // scopable, categorized in 1 tree (master)
        $this->createProduct('scopable', [
            'categories' => ['categoryA1', 'categoryA2'],
            'values'     => [
                'a_scopable_price' => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => [
                            ['amount' => '10.50', 'currency' => 'EUR'],
                            ['amount' => '11.50', 'currency' => 'USD'],
                            ['amount' => '78.77', 'currency' => 'CNY']
                        ]
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['amount' => '10.50', 'currency' => 'EUR'],
                            ['amount' => '11.50', 'currency' => 'USD'],
                            ['amount' => '78.77', 'currency' => 'CNY']
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testProductCreationWithIdenticalIdentifiers()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_create_with_identifier"
    }
JSON;
        $expectedProduct = [
            'identifier'    => 'product_create_with_identifier',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_create_with_identifier'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_create_with_identifier', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_create_with_identifier');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_create_with_identifier',
            $response->headers->get('location')
        );
    }

    public function testProductCreationWithoutIdentifier()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedProduct = [
            'identifier'    => 'product_create_without_identifier',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_create_without_identifier'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_create_without_identifier', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_create_without_identifier');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_create_without_identifier',
            $response->headers->get('location')
        );
    }

    public function testProductCreationWithDifferentIdentifiers()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "bar"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'The identifier "bar" provided in the request body must match the identifier "foo" provided in the url.',
        ];

        $client->request('PATCH', 'api/rest/v1/products/foo', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductPartialUpdateWithIdenticalIdentifiers()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_categories"
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_categories'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_categories');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_categoriese',
            $response->headers->get('location')
        );
    }

    public function testProductPartialUpdateWithoutIdentifier()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_categories'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_categories');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_categories',
            $response->headers->get('location')
        );
    }

    public function testProductPartialUpdateWithTheIdentifierUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "new_product_categories"
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'new_product_categories',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'new_product_categories'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'new_product_categories');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/new_product_categories',
            $response->headers->get('location')
        );

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_categories');
        $this->assertSame(null, $product);
    }

    public function testProductPartialUpdateWithTheFamilyUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "complete",
        "family": "familyA"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/complete', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'complete',
            'family'        => 'familyA',
            'groups'        => ['groupA'],
            'variant_group' => 'variantA',
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'complete']
                ],
                'a_metric' => [
                    ['locale' => null, 'scope' => null, 'data' => ['amount' => '10.0000', 'unit' => 'KILOWATT']],
                ],
                'a_date'   => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'   => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                "PACK"         => ["groups"   => [], "products" => []],
                "SUBSTITUTION" => ["groups"   => [], "products" => []],
                "UPSELL"       => ["groups"   => [], "products" => []],
                "X_SELL"       => ["groups"   => ["groupA"], "products" => ["product_categories"]],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'complete');
    }

    public function testProductPartialUpdateWithTheFamilyDeleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "complete",
        "family": null
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/complete', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'complete',
            'family'        => null,
            'groups'        => ['groupA'],
            'variant_group' => 'variantA',
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'complete']
                ],
                'a_metric' => [
                    ['locale' => null, 'scope' => null, 'data' => ['amount' => '10.0000', 'unit' => 'KILOWATT']],
                ],
                'a_date'   => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'   => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                "PACK"         => ["groups"   => [], "products" => []],
                "SUBSTITUTION" => ["groups"   => [], "products" => []],
                "UPSELL"       => ["groups"   => [], "products" => []],
                "X_SELL"       => ["groups"   => ["groupA"], "products" => ["product_categories"]],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'complete');
    }

    public function testProductPartialUpdateWithTheGroupsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "complete",
        "groups": ['groupB']
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/complete', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'complete',
            'family'        => 'master',
            'groups'        => ['groupB'],
            'variant_group' => 'variantA',
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'complete']
                ],
                'a_metric' => [
                    ['locale' => null, 'scope' => null, 'data' => ['amount' => '10.0000', 'unit' => 'KILOWATT']],
                ],
                'a_date'   => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'   => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                "PACK"         => ["groups"   => [], "products" => []],
                "SUBSTITUTION" => ["groups"   => [], "products" => []],
                "UPSELL"       => ["groups"   => [], "products" => []],
                "X_SELL"       => ["groups"   => ["groupA"], "products" => ["product_categories"]],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'complete');
    }

    public function testProductPartialUpdateWithTheVariantGroupDeleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
                {
        "identifier": "complete",
        "family": null
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/complete', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'complete',
            'family'        => null,
            'groups'        => ['groupA'],
            'variant_group' => 'variantA',
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'complete']
                ],
                'a_metric' => [
                    ['locale' => null, 'scope' => null, 'data' => ['amount' => '10.0000', 'unit' => 'KILOWATT']],
                ],
                'a_date'   => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'   => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                "PACK"         => [
                    "groups"   => [],
                    "products" => [],
                ],
                "SUBSTITUTION" => [
                    "groups"   => [],
                    "products" => [],
                ],
                "UPSELL"       => [
                    "groups"   => [],
                    "products" => [],
                ],
                "X_SELL"       => [
                    "groups"   => ["groupA"],
                    "products" => ["product_categories"],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'complete');
    }

    public function testProductPartialUpdate()
    {
        $client = $this->createAuthenticatedClient();

        $files = [
            'akeneo_pdf' => $this->getFixturePath('akeneo.pdf'),
            'akeneo_jpg' => $this->getFixturePath('akeneo.jpg'),
            'ziggy_png'  => $this->getFixturePath('ziggy.png'),
        ];

        $data =
<<<JSON
    {
        "identifier": "product_categories",
        "groups": ["groupA", "groupB"],
        "variant_group": "variantA",
        "family": "familyA2",
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
                "data": null
            }],
            "a_metric": [{
                "locale": null,
                "scope": null,
                "data": null
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
                    "amount": "45.00",
                    "currency": "USD"
                }, {
                    "amount": "56.53",
                    "currency": "EUR"
                }]
            }],
            "a_price_without_decimal": [{
                "locale": null,
                "scope": null,
                "data": [{
                    "amount": -45,
                    "currency": "USD"
                }, {
                    "amount": 56,
                    "currency": "EUR"
                }]
            }],
            "a_ref_data_multi_select": [{
                "locale": null,
                "scope": null,
                "data": ["airguard", "braid"]
            }],
            "a_ref_data_product_categories_select": [{
                "locale": null,
                "scope": null,
                "data": "bright-lilac"
            }],
            "a_product_categories_select": [{
                "locale": null,
                "scope": null,
                "data": "optionB"
            }],
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "this is a text"
            }],
            "a_text_area": [{
                "locale": null,
                "scope": null,
                "data": "this is a very very very very very long  text"
            }],
            "a_yes_no": [{
                "locale": null,
                "scope": null,
                "data": true
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

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => 'familyA2',
            'groups'        => ['groupA', 'groupB'],
            'variant_group' => 'variantA',
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                'sku'                                => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_categories'],
                ],
                'a_text'                             => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'A name',
                    ],
                ],
                'a_file'                             => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt',
                    ],
                ],
                'an_image'                           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_ziggy.png',
                    ],
                ],
                'a_date'                             => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_metric_without_decimal'           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => 98, 'unit' => 'CENTIMETER'],
                    ],
                ],
                'a_metric_without_decimal_negative'  => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => -20, 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_metric_negative'                  => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_multi_select'                     => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'a_number_float'                     => [
                    ['locale' => null, 'scope' => null, 'data' => '12.5678'],
                ],
                'a_number_float_negative'            => [
                    ['locale' => null, 'scope' => null, 'data' => '-99.8732'],
                ],
                'a_number_integer'                   => [
                    ['locale' => null, 'scope' => null, 'data' => 42],
                ],
                'a_number_integer_negative'          => [
                    ['locale' => null, 'scope' => null, 'data' => -42],
                ],
                'a_price'                            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '45.00', 'currency' => 'USD'],
                            ['amount' => '56.53', 'currency' => 'EUR'],
                        ],
                    ],
                ],
                'a_price_without_decimal'            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => -45, 'currency' => 'USD'],
                            ['amount' => 56, 'currency' => 'EUR'],
                        ],
                    ],
                ],
                'a_ref_data_multi_select'            => [
                    ['locale' => null, 'scope' => null, 'data' => ['airguard', 'braid']],
                ],
                'a_ref_data_product_categories_select'           => [
                    ['locale' => null, 'scope' => null, 'data' => 'bright-lilac'],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text_area'                        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no'                           => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'a_localizable_scopable_image'       => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_ziggy_en_US.png',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_akeneo_fr_FR.jpg',
                    ],
                ],
                'a_scopable_price'                   => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => [
                            ['amount' => '15.00', 'currency' => 'EUR'],
                            ['amount' => '20.00', 'currency' => 'USD'],
                        ],
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['amount' => '17.00', 'currency' => 'EUR'],
                            ['amount' => '24.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'a text area for ecommerce in English',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'tablet',
                        'data'   => 'a text area for tablets in English',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => 'une zone de texte pour les tablettes en français',
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_categories');
    }

    public function testProductCreationWithGroups()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_creation_groups",
        "groups": ["groupA", "groupB"]
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_creation_groups',
            'family'        => null,
            'groups'        => ["groupA", "groupB"],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_creation_groups'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_groups');
    }

    public function testProductCreationWithVariantGroup()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_creation_variant_group",
        "variant_group": "variantA",
        "values": {
           "a_product_categories_select": [{
                "locale": null,
                "scope": null,
                "data": "optionB"
           }]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_creation_variant_group',
            'family'        => null,
            'groups'        => [],
            'variant_group' => "variantA",
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku'             => [[
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'product_creation_variant_group',
                ]],
                'a_product_categories_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'          => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_variant_group');
    }

    public function testProductCreationWithCategories()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_creation_categories",
        "categories": ["master", "categoryA"]
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_creation_categories',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => ["categoryA", "master"],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_creation_categories'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_categories');
    }

    public function testProductCreationWithAssociations()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_creation_associations",
        "associations": {
            "X_SELL": {
                "groups": ["groupA"],
                "products": ["product_categories"]
            }
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_creation_associations',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_creation_associations'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                "PACK"         => [
                    "groups"   => [],
                    "products" => [],
                ],
                "SUBSTITUTION" => [
                    "groups"   => [],
                    "products" => [],
                ],
                "UPSELL"       => [
                    "groups"   => [],
                    "products" => [],
                ],
                "X_SELL"       => [
                    "groups"   => ["groupA"],
                    "products" => ["product_categories"],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_associations');
    }

    public function testProductCreationWithProductValues()
    {
        $client = $this->createAuthenticatedClient();

        $files = [
            'akeneo_pdf' => $this->getFixturePath('akeneo.pdf'),
            'akeneo_jpg' => $this->getFixturePath('akeneo.jpg'),
            'ziggy_png'  => $this->getFixturePath('ziggy.png'),
        ];

        $data =
<<<JSON
    {
        "identifier": "product_creation_product_values",
        "groups": ["groupA", "groupB"],
        "variant_group": "variantA",
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
                    "amount": "45.00",
                    "currency": "USD"
                }, {
                    "amount": "56.53",
                    "currency": "EUR"
                }]
            }],
            "a_price_without_decimal": [{
                "locale": null,
                "scope": null,
                "data": [{
                    "amount": -45,
                    "currency": "USD"
                }, {
                    "amount": 56,
                    "currency": "EUR"
                }]
            }],
            "a_ref_data_multi_select": [{
                "locale": null,
                "scope": null,
                "data": ["airguard", "braid"]
            }],
            "a_ref_data_product_categories_select": [{
                "locale": null,
                "scope": null,
                "data": "bright-lilac"
            }],
            "a_product_categories_select": [{
                "locale": null,
                "scope": null,
                "data": "optionB"
            }],
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "this is a text"
            }],
            "a_text_area": [{
                "locale": null,
                "scope": null,
                "data": "this is a very very very very very long  text"
            }],
            "a_yes_no": [{
                "locale": null,
                "scope": null,
                "data": true
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

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_creation_product_values',
            'family'        => 'familyA',
            'groups'        => ['groupA', 'groupB'],
            'variant_group' => 'variantA',
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_creation_product_values'],
                ],
                'a_file'                             => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt',
                    ],
                ],
                'an_image'                           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_ziggy.png',
                    ],
                ],
                'a_date'                             => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_metric'                           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                    ],
                ],
                'a_metric_without_decimal' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => 98, 'unit' => 'CENTIMETER'],
                    ],
                ],
                'a_metric_without_decimal_negative' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => -20, 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_metric_negative'        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_multi_select'                     => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'a_number_float'                     => [
                    ['locale' => null, 'scope' => null, 'data' => '12.5678'],
                ],
                'a_number_float_negative'            => [
                    ['locale' => null, 'scope' => null, 'data' => '-99.8732'],
                ],
                'a_number_integer'                   => [
                    ['locale' => null, 'scope' => null, 'data' => 42]
                ],
                'a_number_integer_negative' => [
                    ['locale' => null, 'scope' => null, 'data' => -42]
                ],
                'a_price'                            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '45.00', 'currency' => 'USD'],
                            ['amount' => '56.53', 'currency' => 'EUR']
                        ],
                    ],
                ],
                'a_price_without_decimal'            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => -45, 'currency' => 'USD'],
                            ['amount' => 56, 'currency' => 'EUR']
                        ],
                    ],
                ],
                'a_ref_data_multi_select'            => [
                    ['locale' => null, 'scope' => null, 'data' => ['airguard', 'braid']]
                ],
                'a_ref_data_product_categories_select'           => [
                    ['locale' => null, 'scope' => null, 'data' => 'bright-lilac'],
                ],
                'a_product_categories_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'                             => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'A name',
                    ],
                ],
                'a_text_area'                        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no'                           => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'a_localizable_scopable_image'                => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_ziggy_en_US.png',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_akeneo_fr_FR.jpg',
                    ],
                ],
                'a_scopable_price'                   => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => [
                            ['amount' => '15.00', 'currency' => 'EUR'],
                            ['amount' => '20.00', 'currency' => 'USD'],
                        ],
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['amount' => '17.00', 'currency' => 'EUR'],
                            ['amount' => '24.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'a text area for ecommerce in English',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'tablet',
                        'data'   => 'a text area for tablets in English'
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => 'une zone de texte pour les tablettes en français',
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
        $this->assertSameProducts($expectedProduct, 'product_creation_product_values');
    }

    public function testProductCreationWithIgnoredProperties()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "foo",
        "created": "2014-06-14T13:12:50+02:00",
        "updated": "2014-06-14T13:12:50+02:00"
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'foo',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'foo'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $response = $client->getResponse();


        $this->assertSame('', $response->getContent());
        $this->assertSameProducts($expectedProduct, 'foo');
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $normalizer = $this->get('pim_catalog.normalizer.standard.product');
        $standardizedProduct = $normalizer->normalize($product);

        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['created']);
        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['updated']);
    }

    public function testResponseWhenIdentifierPropertyNotEqualsToIdentifierInValues()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "different",
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }]
         }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/different',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testResponseWhenMissingIdentifierPropertyAndProvidedIdentifierInValues()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }]
         }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'values[sku].varchar',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenIdentifierIsNotFilled()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'values[sku].varchar',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenProductAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_family"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'values[sku]',
                    'message' => 'The value product_family is already set on another product for the unique attribute sku',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "extra_property": "foo"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_categories', [], [], [], $data);

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/products.html', $version),
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier identifier of the product that should be created
     */
    protected function assertSameProducts(array $expectedProduct, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $normalizer = $this->get('pim_catalog.normalizer.standard.product');
        $standardizedProduct = $normalizer->normalize($product);

        $standardizedProduct = static::sanitizeDateFields($standardizedProduct);
        $expectedProduct = static::sanitizeDateFields($expectedProduct);

        $standardizedProduct = static::sanitizeMediaAttributeData($standardizedProduct);
        $expectedProduct = static::sanitizeMediaAttributeData($expectedProduct);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            true
        );
    }
}
