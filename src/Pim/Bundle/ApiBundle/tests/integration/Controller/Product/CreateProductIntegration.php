<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class CreateProductIntegration extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createProduct('simple', []);
    }

    public function testHttpHeadersInResponseWhenAProductIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_create_headers"
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_create_headers',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testProductCreationWithFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_creation_family",
        "family": "familyA"
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_creation_family',
            'family'        => 'familyA',
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_creation_family'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_family');
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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
           "a_simple_select": [{
                "locale": null,
                "scope": null,
                "data": "optionB"
           }]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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
                'a_simple_select' => [
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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
                "products": ["simple"]
            }
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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
                "X_SELL"       => [
                    "groups"   => ["groupA"],
                    "products" => ["simple"],
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
            "a_ref_data_simple_select": [{
                "locale": null,
                "scope": null,
                "data": "bright-lilac"
            }],
            "a_simple_select": [{
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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
                'a_ref_data_simple_select'           => [
                    ['locale' => null, 'scope' => null, 'data' => 'bright-lilac'],
                ],
                'a_simple_select'                    => [
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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

    public function testProductCreationWithIdenticalIdentifiers()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "same_identifier",
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "same_identifier"
            }]
         }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/same_identifier',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property'   => 'identifier',
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'This value should not be blank.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenAttributeValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "wrong_amount",
        "variant_group": "variantB",
        "values": {
            "a_scopable_price": [{
                "locale": null,
                "scope": "ecommerce",
                "data": [
                    { "amount": "string", "currency": "EUR" },
                    { "amount": 12, "currency": "USD" }
                ]
            }],
            "a_number_float_negative":[{
                "locale": null,
                "scope": null,
                "data": -300
            }]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "variant_group",
            "message": "The product \"wrong_amount\" is in the variant group \"variantB\" but it misses the following axes: a_simple_select."
        },
        {
            "property": "variant_group",
            "message": "Product \"wrong_amount\" should have value for axis \"a_simple_select\" of variant group \"Variant B\""
        },
        {
            "property": "values",
            "message": "This value should be a valid number.",
            "attribute": "a_scopable_price",
            "locale": null,
            "scope": "ecommerce",
            "currency": "EUR"
        },
        {
            "property": "values",
            "message": "This value should be -250 or more.",
            "attribute": "a_number_float_negative",
            "locale": null,
            "scope": null
        }
    ]
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenProductAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "simple"
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'The value simple is already set on another product for the unique attribute sku',
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

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_products'
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenSettingProductValueWithAnUnknownAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "foo",
        "family": null,
        "groups": ["groupA"],
        "variant_group": null,
        "categories": ["master"],
        "values": {
            "unknown_attribute":[{
                "locale": null,
                "scope": null,
                "data": true
            }]
        },
        "associations": {
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "unknown_attribute" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_products'
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

        ksort($expectedProduct['values']);
        ksort($standardizedProduct['values']);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}
