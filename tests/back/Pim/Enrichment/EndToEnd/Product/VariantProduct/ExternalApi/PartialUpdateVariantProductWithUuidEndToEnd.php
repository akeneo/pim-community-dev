<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateVariantProductWithUuidEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;

    /** @var Collection */
    private $products;

    public function getUuidFromIdentifier(): UuidInterface
    {
        return $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optionb_false')->getUuid();
    }

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

        $this->createProductModel(
            [
                'code' => 'apollon',
                'parent' => 'test',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ],
                ],
            ]
        );

        // apollon_blue_m & apollon_blue_l, categorized in 2 trees (master and categoryA1)
        $this->createVariantProduct('apollon_optionb_false', [
            new SetCategories(['master']),
            new ChangeParent('amor'),
            new SetGroups(['groupA']),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    public function testProductVariantCreationWithIdenticalUuids(): void
    {
        $client = $this->createAuthenticatedClient();

        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "family": "familyA",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "product_variant_create_with_identifier" }]
        }
    }
JSON;
        $expectedProduct = [
            'identifier'    => 'product_variant_create_with_identifier',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_create_with_identifier'],
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
            'quantified_associations' => [],
        ];

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_create_with_identifier');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            "http://localhost/api/rest/v1/products-uuid/{$uuid->toString()}",
            $response->headers->get('location')
        );
    }

    public function testProductVariantCreationWithoutUuid(): void
    {
        $client = $this->createAuthenticatedClient();

        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
    {
        "family": "familyA",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "product_variant_create_with_identifier" }]
        }
    }
JSON;
        $expectedProduct = [
            'identifier'    => 'product_variant_create_with_identifier',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_variant_create_with_identifier'],
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
            'quantified_associations' => [],
        ];

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_create_with_identifier');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            "http://localhost/api/rest/v1/products-uuid/{$uuid->toString()}",
            $response->headers->get('location')
        );
    }

    public function testProductVariantCreationWithDifferentUuids(): void
    {
        $client = $this->createAuthenticatedClient();

        $uuid = Uuid::uuid4();
        $otherUuid = Uuid::uuid4();

        $data =
            <<<JSON
            {
                "uuid": "{$otherUuid->toString()}",
                "family": "familyA",
                "parent": "amor",
                "values": {
                  "a_yes_no": [
                    {
                      "locale": null,
                      "scope": null,
                      "data": false
                    }
                  ],
                  "sku": [{"locale": null, "scope": null, "data": "product_variant_create_with_identifier" }]
                }
            }
            JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => "The uuid \"{$otherUuid->toString()}\" provided in the request body must match the uuid \"{$uuid->toString()}\" provided in the url.",
        ];

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantPartialUpdateCannotUpdateFamily(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "family": "familyA2",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

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

    public function testProductVariantPartialUpdateCannotSetFamilyToNull(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "parent": "amor",
        "family": null,
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The family cannot be "null" because your product with the apollon_optionb_false identifier is a variant product.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantPartialUpdateCannotSetFamilyToNullNoMatterTheOrder(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "family": null,
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The family cannot be "null" because your product with the apollon_optionb_false identifier is a variant product.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantPartialUpdateWithTheGroupsUpdated(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": ["groupB", "groupA"],
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWithTheGroupsDeleted(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => ["master"],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWithTheCategoriesUpdated(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": ["categoryA"],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => ["categoryA"],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWithTheCategoriesDeleted(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWithTheAssociationsUpdated(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        },
        "associations": {
            "PACK": {
                "groups": ["groupA"],
                "products": ["{$uuid->toString()}"]
            },
            "SUBSTITUTION": {
                "product_models": ["amor"]
            }
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => [
                    'groups' => ['groupA'],
                    'product_uuids' => [$this->getProductUuid('apollon_optionb_false')->toString()],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => ['amor'],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWithTheAssociationsDeletedOnGroups(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        },
        "associations": {
            "X_SELL": {
                "groups": []
            }
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'         => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                'UPSELL'       => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                'X_SELL'       => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheAssociationsDeleted(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        },
        "associations": {
        "X_SELL": {
            "groups": [],
            "products": []
            }
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'         => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                'UPSELL'       => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                'X_SELL'       => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWithProductDisable(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "enabled": false,
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => false,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWhenProductValueAddedOnAttribute(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $akeneoJpgPath = $this->getFixturePath('akeneo.jpg');

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_localizable_image": [{
              "locale": "zh_CN",
              "scope": null,
              "data": "${akeneoJpgPath}"
          }],
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductVariantPartialUpdateWhenProductValueDeletedOnAttribute(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": [],
        "parent": "amor",
        "categories": [],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": null
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

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

    public function testProductVariantPartialUpdateOnMultipleAttributes(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $files = [
            'akeneo_pdf' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')),
            'akeneo_jpg' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
            'ziggy_png'  => $this->getFileInfoKey($this->getFixturePath('ziggy.png')),
        ];

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "parent": "amor",
        "groups": ["groupA", "groupB"],
        "family": "familyA",
        "categories": ["master", "categoryA"],
        "values": {
            "a_metric": [{
                "locale": null,
                "scope": null,
                "data": null
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
            "a_yes_no": [
              {
                "locale": null,
                "scope": null,
                "data": false
              }
            ],
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
            "a_localizable_scopable_image": [{
                "locale": "en_US",
                "scope": "ecommerce",
                "data": "${files['ziggy_png']}"
            }, {
                "locale": "fr_FR",
                "scope": "tablet",
                "data": "${files['akeneo_jpg']}"
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
            }],
            "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => 'familyA',
            'parent'        => "amor",
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantCannotUpdateCommonAttribute(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $files = [
            'akeneo_pdf' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')),
            'akeneo_jpg' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
            'ziggy_png'  => $this->getFileInfoKey($this->getFixturePath('ziggy.png')),
        ];

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "groups": ["groupA", "groupB"],
        "family": "familyA",
        "categories": ["master", "categoryA"],
        "values": {
            "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }],
            "a_metric": [{
                "locale": null,
                "scope": null,
                "data": null
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
            "a_yes_no": [
              {
                "locale": null,
                "scope": null,
                "data": false
              }
            ],
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
            "a_localizable_scopable_image": [{
                "locale": "en_US",
                "scope": "ecommerce",
                "data": "${files['ziggy_png']}"
            }, {
                "locale": "fr_FR",
                "scope": "tablet",
                "data": "${files['akeneo_jpg']}"
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

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => 'familyA',
            'parent'        => "amor",
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithIgnoredProperties(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        },
        "created": "2014-06-14T13:12:50+02:00",
        "updated": "2014-06-14T13:12:50+02:00"
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => ['groupA'],
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
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
                        "data"   => false,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();


        $this->assertSame('', $response->getContent());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optionb_false');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['created']);
        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['updated']);
    }

    public function testPartialUpdateResponseWhenIdentifierPropertyNotEqualsToIdentifierInValues(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "parent": "amor",
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }],
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
         }
    }
JSON;

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            "http://localhost/api/rest/v1/products-uuid/{$uuid->toString()}",
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testProductVariantPartialUpdateWithTheParentUpdated(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "uuid": "{$uuid->toString()}",
        "family": "familyA",
        "parent": "apollon",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ],
          "sku": [{"locale": null, "scope": null, "data": "apollon_optionb_false" }]
        }
    }
JSON;
        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "apollon",
            'groups'        => ['groupA'],
            'categories'    => ['master'],
            'enabled'       => true,
            'values'        => [
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
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                ],
                "a_yes_no" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => false,
                    ],
                ],
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            "http://localhost/api/rest/v1/products-uuid/{$uuid->toString()}",
            $response->headers->get('location')
        );
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testPartialUpdateResponseWhenMissingIdentifierPropertyAndProvidedIdentifierInValues(): void
    {
        $client = $this->createAuthenticatedClient();
        $uuid = $this->getUuidFromIdentifier();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "foo"
            }],
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

        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid->toString()}", [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            "http://localhost/api/rest/v1/products-uuid/{$uuid->toString()}",
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
