<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateProductVariantIntegration extends AbstractProductTestCase
{
    /** @var Collection */
    private $products;

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

        // apollon_blue_m & apollon_blue_l, categorized in 2 trees (master and categoryA1)
        $this->createVariantProduct('apollon_optionb_false', [
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

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    public function testProductVariantCreationWithIdenticalIdentifiers(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "product_variant_create_with_identifier",
        "family": "familyA",
        "parent": "amor",
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
                        "data"   => false,
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $client->request('PATCH', 'api/rest/v1/products/product_variant_create_with_identifier', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_variant_create_with_identifier');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_variant_create_with_identifier',
            $response->headers->get('location')
        );
    }

    public function testProductVariantCreationWithoutIdentifier(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedProduct = [
            'identifier'    => 'product_create_without_identifier',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
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

    public function testProductVariantCreationWithDifferentIdentifiers(): void
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

    public function testProductVariantPartialUpdateWithTheIdentifierUpdatedWithNull(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": null,
        "parent": "amor",
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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

    public function testProductVariantPartialUpdateCannotUpdateFamily(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "family": "familyA2",
        "parent": "amor",
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "parent": "amor",
        "family": null,
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The family can\'t be "null" because your product with the identifier "apollon_optionb_false" is a variant product.',
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

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "family": null,
        "parent": "amor",
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family',
                    'message'  => 'The family can\'t be "null" because your product with the identifier "apollon_optionb_false" is a variant product.',
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

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "groups": ["groupB", "groupA"],
        "parent": "amor",
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheGroupsDeleted(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "groups": [],
        "parent": "amor",
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheCategoriesUpdated(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheCategoriesDeleted(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheAssociationsUpdated(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        },
        "associations": {
            "PACK": {
                "groups": ["groupA"],
                "products": ["apollon_optionb_false"]
            }
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
                'PACK'         => ['groups'   => ['groupA'], 'products' => ['apollon_optionb_false']],
                'SUBSTITUTION' => ['groups'   => [], 'products' => []],
                'UPSELL'       => ['groups'   => [], 'products' => []],
                'X_SELL'       => ['groups'   => [], 'products' => []],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheAssociationsDeletedOnGroups(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        },
        "associations": {
            "X_SELL": {
                "groups": []
            }
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
                'PACK'         => ['groups' => [], 'products' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => []],
                'UPSELL'       => ['groups' => [], 'products' => []],
                'X_SELL'       => ['groups' => [], 'products' => []]
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithTheAssociationsDeleted(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        },
        "associations": {
        "X_SELL": {
            "groups": [],
            "products": []
            }
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
                'PACK'         => ['groups' => [], 'products' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => []],
                'UPSELL'       => ['groups' => [], 'products' => []],
                'X_SELL'       => ['groups' => [], 'products' => []]
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithProductDisable(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }


    public function testProductVariantPartialUpdateNewAxisValues(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "enabled": false,
        "groups": [],
        "parent": "amor",
        "categories": [],
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'attribute',
                    'message'  => 'Variant axis "a_yes_no" cannot be modified, "true" given',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantPartialUpdateWhenProductValueAddedOnAttribute(): void
    {
        $client = $this->createAuthenticatedClient();

        $akeneoJpgPath = $this->getFixturePath('akeneo.jpg');

        $data =
            <<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWhenProductValueDeletedOnAttribute(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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

        $files = [
            'akeneo_pdf' => $this->getFixturePath('akeneo.pdf'),
            'akeneo_jpg' => $this->getFixturePath('akeneo.jpg'),
            'ziggy_png'  => $this->getFixturePath('ziggy.png'),
        ];

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
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
            }]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateSetParentToNull(): void
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
        "identifier": "apollon_optionb_false",
        "parent": null
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "parent" cannot be modified, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => ["documentation" => ["href" => "http://api.akeneo.com/api-reference.html#patch_products__code_"]]
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductVariantCannotUpdateCommonAttribute(): void
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
        "identifier": "apollon_optionb_false",
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
            }]
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

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
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
    }

    public function testProductVariantPartialUpdateWithIgnoredProperties(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
        "parent": "amor",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ]
        },
        "created": "2014-06-14T13:12:50+02:00",
        "updated": "2014-06-14T13:12:50+02:00"
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'apollon_optionb_false',
            'family'        => "familyA",
            'parent'        => "amor",
            'groups'        => [],
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
        ];

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $response = $client->getResponse();


        $this->assertSame('', $response->getContent());
        $this->assertSameProducts($expectedProduct, 'apollon_optionb_false');
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optionb_false');
        $standardizedProduct = $this->get('pim_serializer')->normalize($product, 'standard');

        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['created']);
        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['updated']);
    }

    public function testPartialUpdateResponseWhenIdentifierPropertyNotEqualsToIdentifierInValues(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "identifier": "apollon_optionb_false",
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
          ]
         }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/apollon_optionb_false',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testPartialUpdateResponseWhenMissingIdentifierPropertyAndProvidedIdentifierInValues(): void
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

        $client->request('PATCH', 'api/rest/v1/products/apollon_optionb_false', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/apollon_optionb_false',
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
