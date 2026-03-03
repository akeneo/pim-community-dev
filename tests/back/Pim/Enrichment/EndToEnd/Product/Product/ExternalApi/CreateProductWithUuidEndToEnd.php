<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class CreateProductWithUuidEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;
    use QuantifiedAssociationsTestCaseTrait;

    private UuidInterface $existingUuid;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->existingUuid = $this->createProduct('simple', [])->getUuid();
    }

    public function testHttpHeadersInResponseWhenAProductIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [
                {"locale": null, "scope": null, "data": "product_create_headers"}
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            sprintf(
                'http://localhost/api/rest/v1/products-uuid/%s',
                $this->getProductUuid('product_create_headers')->toString()
            ),
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
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "product_creation_family"
            }]
        },
        "family": "familyA"
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'uuid'          => $this->getProductUuid('product_creation_family')->toString(),
            'identifier'    => 'product_creation_family',
            'family'        => 'familyA',
            'parent'        => null,
            'groups'        => [],
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
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_family');

        $this->assertEventCount(1, ProductCreated::class);
    }

    public function testProductCreationWithGroups()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "product_creation_groups"
            }]
        },
        "groups": ["groupA", "groupB"]
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'uuid'          => $this->getProductUuid('product_creation_groups')->toString(),
            'identifier'    => 'product_creation_groups',
            'family'        => null,
            'parent'        => null,
            'groups'        => ["groupA", "groupB"],
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
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_groups');
    }

    public function testProductCreationWithCategories()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "product_creation_categories"
            }]
        },
        "categories": ["master", "categoryA"]
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'uuid'          => $this->getProductUuid('product_creation_categories')->toString(),
            'identifier'    => 'product_creation_categories',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
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
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_creation_categories');
    }

    public function testItCreatesAProductWithParent()
    {
        $this->createProductModel([
            'code' => 'a_product_model',
            'family_variant' => 'familyVariantA1',
            'values'  => [],
        ]);
        $this->createProductModel([
            'code' => 'a_sub_product_model',
            'parent' => 'a_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
            ],
        ]);

        $client = $this->createAuthenticatedClient();
        $data = <<<JSON
{
    "values": {
        "sku": [
            {"locale": null, "scope": null, "data": "foo"}
        ],
        "a_yes_no": [
            { "locale": null, "scope": null, "data": true }
        ]
    },
    "parent": "a_sub_product_model"
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $this->assertSameProducts([
            'uuid' => $this->getProductUuid('foo')->toString(),
            'identifier' => 'foo',
            'family' => 'familyA',
            'parent' => 'a_sub_product_model',
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'a_simple_select' => [['data' => 'optionB', 'locale' => null, 'scope' => null]],
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                'sku' => [['data' => 'foo', 'locale' => null, 'scope' => null]],
            ],
            'created' => 'this is a date formatted to ISO-8601',
            'updated' => 'this is a date formatted to ISO-8601',
            'associations' => [],
            'quantified_associations' => [],
        ], 'foo');
    }

    public function testProductCreationWithAssociations()
    {
        $this->createQuantifiedAssociationType('QUANTIFIEDASSOCIATION');

        $this->createProductModel([
            'code' => 'a_product_model',
            'family_variant' => 'familyVariantA1',
            'values'  => [],
        ]);

        $client = $this->createAuthenticatedClient();
        $simpleUuid = $this->getProductUuid('simple')->toString();

        $data = <<<JSON
{
    "values": {
        "sku": [{
            "locale": null,
            "scope": null,
            "data": "product_creation_associations"
        }]
    },
    "associations": {
        "UPSELL": {
            "product_models": ["a_product_model"]
        },
        "X_SELL": {
            "groups": ["groupA"],
            "products": ["$simpleUuid"]
        }
    },
    "quantified_associations": {
        "QUANTIFIEDASSOCIATION": {
            "products": [{"quantity": 12, "uuid": "$simpleUuid"}]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'uuid'          => $this->getProductUuid('product_creation_associations')->toString(),
            'identifier'    => 'product_creation_associations',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
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
                'PACK'         => [
                    'groups'   => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups'   => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'UPSELL'       => [
                    'groups'   => [],
                    'product_uuids' => [],
                    'product_models' => ['a_product_model'],
                ],
                'X_SELL'       => [
                    'groups'   => ['groupA'],
                    'product_uuids' => [$this->getProductUuid('simple')->toString()],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [
                'QUANTIFIEDASSOCIATION' => [
                    'products' => [[
                        'uuid' => $this->getProductUuid('simple')->toString(),
                        'identifier' => 'simple',
                        'quantity' => 12,
                    ]],
                    'product_models' => [],
                ]
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
            'akeneo_pdf' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')),
            'akeneo_jpg' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
            'ziggy_png'  => $this->getFileInfoKey($this->getFixturePath('ziggy.png')),
        ];

        $data =
            <<<JSON
    {
        "groups": ["groupA", "groupB"],
        "family": "familyA",
        "categories": ["master", "categoryA"],
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "product_creation_product_values"
            }],
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
                    "unit": "Kilowatt"
                }
            }],
            "a_metric_without_decimal": [{
                "locale": null,
                "scope": null,
                "data": {
                    "amount": 98,
                    "unit": "CentiMeter"
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
                "data": "12.56"
            }],
            "a_number_float_very_decimal": [{
                "locale": null,
                "scope": null,
                "data": "12.56787697870"
            }],
            "a_number_float_negative": [{
                "locale": null,
                "scope": null,
                "data": "-99.873200000000"
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
                "data": "optionB"
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'uuid'          => $this->getProductUuid('product_creation_product_values')->toString(),
            'identifier'    => 'product_creation_product_values',
            'family'        => 'familyA',
            'parent'        => null,
            'groups'        => ['groupA', 'groupB'],
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
                    ['locale' => null, 'scope' => null, 'data' => '12.5600'],
                ],
                'a_number_float_very_decimal'                     => [
                    ['locale' => null, 'scope' => null, 'data' => '12.5678769787'],
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
                            ['amount' => '56.53', 'currency' => 'EUR'],
                            ['amount' => '45.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_price_without_decimal'            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => 56, 'currency' => 'EUR'],
                            ['amount' => -45, 'currency' => 'USD'],
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
            'associations'  => [],
            'quantified_associations' => [],
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
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }]
        },
        "created": "2014-06-14T13:12:50+02:00",
        "updated": "2014-06-14T13:12:50+02:00"
    }
JSON;
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'uuid'          => $this->getProductUuid('foo')->toString(),
            'identifier'    => 'foo',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
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
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSameProducts($expectedProduct, 'foo');
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['created']);
        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['updated']);
    }

    public function testItCreatesWithUuid()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "uuid": "a48ca2b8-656d-4b2c-b9cc-b2243e876ebf",
        "values": {
            "sku": [
                {"locale": null, "scope": null, "data": "foo"}
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products-uuid/a48ca2b8-656d-4b2c-b9cc-b2243e876ebf',
            $response->headers->get('location')
        );

        $this->assertSame($this->getProductUuid('foo')->toString(), 'a48ca2b8-656d-4b2c-b9cc-b2243e876ebf');
    }

    public function testItCreatesWithUppercaseUuid()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "uuid": "A48CA2B8-656D-4b2c-b9cc-b2243e876ebf",
        "values": {
            "sku": [
                {"locale": null, "scope": null, "data": "foo"}
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products-uuid/a48ca2b8-656d-4b2c-b9cc-b2243e876ebf',
            $response->headers->get('location')
        );

        $this->assertSame($this->getProductUuid('foo')->toString(), 'a48ca2b8-656d-4b2c-b9cc-b2243e876ebf');
    }

    public function testResponseWhenIdentifierIsFilled()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{
            "identifier": "foo"
        }';

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "identifier" does not exist. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_products_uuid'
                ]
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenUuidAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "uuid": "{$this->existingUuid->toString()}"
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'uuid',
                    'message'  => \sprintf(
                        'The %s uuid is already used for another product.',
                        $this->existingUuid->toString()
                    ),
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenIdentifierAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "simple"
            }]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'The simple identifier is already used for another product.',
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
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }]
        },
        "extra_property": "foo"
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_products_uuid'
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
        "family": null,
        "groups": ["groupA"],
        "categories": ["master"],
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }],
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $expectedContent = [
            'code'    => 422,
            'message' => 'The unknown_attribute attribute does not exist in your PIM. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_products_uuid'
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6876
     */
    public function testSuccessfullyToCreateProductWithControlCharacter()
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
            }],
            "a_text":[
                {"locale": null, "scope": null, "data": "15\u001fm"}
            ]
        }
    }
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $client->request('GET', sprintf(
            '/api/rest/v1/products-uuid/%s',
            $this->getProductUuid('foo')->toString()
        ));
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');

        $this->assertSame('15' . chr(31) . 'm', $product->getValue('a_text')->getData());
        $this->assertSame('15' . chr(31) . 'm', $product->getRawValues()['a_text']['<all_channels>']['<all_locales>']);
    }

    public function testResponseWhenAssociatingToNonExistingProduct()
    {
        $client = $this->createAuthenticatedClient();
        $nonExistingUuid = Uuid::uuid4();

        $data = <<<JSON
{
    "values": {
        "sku": [{
            "locale": null,
            "scope": null,
            "data": "foo"
        }]
    },
    "associations": {
        "X_SELL": {
            "products": ["$nonExistingUuid"]
        }
    }
}
JSON;

        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"associations\" expects a valid product uuid. The product does not exist, \"$nonExistingUuid\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http:\/\/api.akeneo.com\/api-reference.html#post_products_uuid"
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testResponseWhenAssociatingToNonExistingProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "values": {
        "sku": [{
            "locale": null,
            "scope": null,
            "data": "foo"
        }]
    },
    "associations": {
        "X_SELL": {
            "product_models": ["a_non_exiting_product_model"]
        }
    }
}
JSON;

        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"associations\" expects a valid product model identifier. The product model does not exist, \"a_non_exiting_product_model\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http:\/\/api.akeneo.com\/api-reference.html#post_products_uuid"
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testAccessDeniedWhenCreatingProductWithoutTheAcl()
    {
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_edit');

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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
