<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class CreateVariantProductWithUuidEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;

    private UuidInterface $simpleProductUuid;

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
                        "data" => ["data" => [['amount' => '50', 'currency' => 'EUR']], "locale" => null, "scope" => null],
                    ],
                    'a_number_float'  => [["data" => '12.5', "locale" => null, "scope" => null]],
                    'a_localized_and_scopable_text_area'  => [["data" => 'my pink tshirt', "locale" => 'en_US', "scope" => 'ecommerce']],
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
                        ["locale" => null, "scope" => null, "data" => 'optionB'],
                    ],
                ],
            ]
        );

        $this->simpleProductUuid = $this->createVariantProduct('simple', [
            new ChangeParent('amor'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ])->getUuid();
    }

    public function testProductVariantCreationWithFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "family": "familyA",
        "parent": "amor",
        "values": {
          "sku": [
              {"locale": null, "scope": null, "data": "product_variant_creation_family"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'product_variant_creation_family',
            'family'        => 'familyA',
            'parent'        => 'amor',
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'product_variant_creation_family']
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
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
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_creation_family');

        $this->assertEventCount(1, ProductCreated::class);
    }

    public function testProductVariantCreationWithFamilyNotSpecifiedInSentData()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "parent": "amor",
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_family"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'product_variant_creation_family',
            'family'        => 'familyA',
            'parent'        => 'amor',
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'product_variant_creation_family'],
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
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
            'quantified_associations' => [],
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
        "parent": "amor",
        "family": null,
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_family"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The family cannot be "null" because your product with the product_variant_creation_family identifier is a variant product.',
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
        "parent": "amor",
        "family": "familyA2",
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_family"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

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
        "parent": "invalid",
        "family": "familyA2",
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_family"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "parent" expects a valid parent code. The parent product model does not exist, "invalid" given. Check the expected format on the API documentation.',
            '_links'  => ["documentation" => ["href" => "http://api.akeneo.com/api-reference.html#post_products_uuid"]]
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
        "parent": "amor",
        "family": "familyA",
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation"}
          ],
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

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

    public function testCreateProductVariantWithSameValuesInTheAxeAttribute()
    {
        $client = $this->createAuthenticatedClient();
        $productUuid = Uuid::uuid4()->toString();
        $data =
            <<<JSON
    {
        "parent": "amor",
        "family": "familyA",
        "uuid": "$productUuid",
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $productUuid2 = Uuid::uuid4()->toString();
        $duplicatedData =
            <<<JSON
    {
        "parent": "amor",
        "family": "familyA",
        "uuid": "$productUuid2",
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

        $client2 = $this->createAuthenticatedClient();
        $client2->request('POST', 'api/rest/v1/products-uuid', [], [], [], $duplicatedData);

        $response2 = $client2->getResponse();

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'attribute',
                    'message'  => sprintf('Cannot set value "1" for the attribute axis "a_yes_no" on variant product "%s", as the variant product "%s" already has this value', $productUuid2, $productUuid),
                ],
            ],
        ];

        $this->assertSame($expectedContent, json_decode($response2->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response2->getStatusCode());
    }

    public function testCreateProductVariantWithNoAxeAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "parent": "amor",
        "family": "familyA",
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_with_missing_axe"}
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

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
        "groups": ["groupA", "groupB"],
        "parent": "amor",
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_groups"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'product_variant_creation_groups',
            'family'        => "familyA",
            'parent'        => 'amor',
            'groups'        => ["groupA", "groupB"],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'product_variant_creation_groups'],
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
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
            'quantified_associations' => [],
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
        "parent": "amor",
        "categories": ["master", "categoryA"],
        "values": {
          "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_categories"}
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'product_variant_creation_categories',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => ["categoryA", "master"],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'product_variant_creation_categories'],
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
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
            'quantified_associations' => [],
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
    "parent": "amor",
    "values": {
        "sku": [
            {"locale": null, "scope": null, "data": "product_variant_creation_associations"}
        ],
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
            "products": ["{$this->simpleProductUuid->toString()}"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'product_variant_creation_associations',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'product_variant_creation_associations'],
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
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
                    'product_models' => ['amor'],
                ],
                'X_SELL'       => [
                    'groups'   => ['groupA'],
                    'product_uuids' => [$this->getProductUuid('simple')->toString()],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [],
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
            'akeneo_pdf' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')),
            'akeneo_jpg' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
            'ziggy_png'  => $this->getFileInfoKey($this->getFixturePath('ziggy.png')),
        ];

        $data =
<<<JSON
    {
        "groups": ["groupA", "groupB"],
        "parent": "amor",
        "family": "familyA",
        "categories": ["master", "categoryA"],
        "values": {
            "sku": [
                {"locale": null, "scope": null, "data": "product_variant_creation_product_values"}
            ],
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'product_variant_creation_product_values',
            'family'        => 'familyA',
            'parent'        => "amor",
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'product_variant_creation_product_values'],
                ],
                'a_number_float' => [
                    ["locale" => null, "scope" => null, "data" => '12.5000'],
                ],
                'a_price' => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            ['amount' => '50.00', 'currency' => 'EUR'],
                        ],
                    ],
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
                ],
                'a_yes_no'                           => [
                    ["locale" => null, "scope" => null, "data" => false],
                ],
                'a_text_area'                           => [
                    ["locale" => null, "scope" => null, "data" => 'this is a very very very very very long  text'],
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
            'associations'  => [],
            'quantified_associations' => [],
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
        "parent": "amor",
        "values": {
           "sku": [
               {"locale": null, "scope": null, "data": "foo"}
           ],
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
            'identifier' => 'foo',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                "sku" => [
                    ["locale" => null, "scope" => null, "data" => 'foo'],
                ],
                'a_simple_select' => [
                    ["locale" => null, "scope" => null, "data" => 'optionB'],
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
            'quantified_associations' => [],
        ];

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

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
            new SetCategories(['master']),
            new ChangeParent('amor'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

        $client = $this->createAuthenticatedClient();

        $product =
<<<JSON
    {
        "parent": "amor",
        "categories": ["master"],
        "values": {
           "sku": [
            {"locale": null, "scope": null, "data": "apollon_option_b_true"}
           ],
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $product);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'The apollon_option_b_true identifier is already used for another product.',
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
        "parent": "test",
        "values": {
           "sku": [
            {"locale": null, "scope": null, "data": "new_product_variant"}
           ],
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'parent',
                    'message'  => 'The variant product cannot have product model "test" as parent, (this product model can only have other product models as children)',
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
