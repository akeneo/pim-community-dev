<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateProductWithUuidEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;

    const PRODUCT_FAMILY_UUID = '23344edd-7785-49c9-b3d0-f3573d814430';
    const PRODUCT_GROUPS_UUID = '1b6a4e6d-271c-489b-aae4-4e080759791d';
    const PRODUCT_CATEGORIES_UUID = '61e2f6df-e687-4140-9f6e-3d9dce0a367e';
    const PRODUCT_ASSOCIATIONS_UUID = '1fd06e3c-d5c2-43a5-8b6c-4a5b68c8993e';
    const PRODUCT_LOCALIZABLE_UUID = '5fa15886-7f37-4f39-a377-5998d1e50f64';
    const PRODUCT_COMPLETE_UUID = '452efe93-0197-4949-8c6e-b86b30480b61';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProductWithUuid(self::PRODUCT_FAMILY_UUID, [
            new SetIdentifierValue('sku', 'product_family'),
            new SetFamily('familyA2')
        ]);

        $this->createProductWithUuid(self::PRODUCT_GROUPS_UUID, [
            new SetIdentifierValue('sku', 'product_groups'),
            new SetGroups(['groupA'])
        ]);

        $this->createProductWithUuid(self::PRODUCT_CATEGORIES_UUID, [
            new SetIdentifierValue('sku', 'product_categories'),
            new SetCategories(['master'])
        ]);

        $this->createProductWithUuid(self::PRODUCT_ASSOCIATIONS_UUID, [
            new SetIdentifierValue('sku', 'product_associations'),
            new AssociateProducts('X_SELL', ['product_categories']),
            new AssociateGroups('X_SELL', ['groupA'])
        ]);

        $this->createProductWithUuid(self::PRODUCT_LOCALIZABLE_UUID, [
            new SetIdentifierValue('sku', 'localizable'),
            new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);

        $this->createProductWithUuid(self::PRODUCT_COMPLETE_UUID, [
            new SetIdentifierValue('sku', 'complete'),
            new SetFamily('familyA2'),
            new SetGroups(['groupA']),
            new SetCategories(['master']),
            new SetMeasurementValue('a_metric', null, null, '10.0000', 'KILOWATT'),
            new SetDateValue('a_date', null, null, new \DateTime('2016-06-13T00:00:00+02:00')),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
            new AssociateProducts('X_SELL', ['product_categories']),
            new AssociateGroups('X_SELL', ['groupA'])
        ]);
    }

    public function testCreateProduct()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [{ "locale": null, "scope": null, "data": "new_identifier" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf('api/rest/v1/products-uuid/%s', $uuid->toString()), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateProductWithSameUuid()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();
        $uuidString = $uuid->toString();

        $data =
            <<<JSON
    {
        "uuid": "$uuidString",
        "values": {
            "sku": [{ "locale": null, "scope": null, "data": "new_identifier" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf('api/rest/v1/products-uuid/%s', $uuid->toString()), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateProductWithSameUppercaseUuid()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();
        $uuidString = \strtoupper($uuid->toString());

        $data =
            <<<JSON
    {
        "uuid": "$uuidString",
        "values": {
            "sku": [{ "locale": null, "scope": null, "data": "new_identifier" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf('api/rest/v1/products-uuid/%s', $uuidString), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateProductWithDifferentUuid()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();
        $newUuid = Uuid::uuid4();
        $uuidString = $newUuid->toString();

        $data =
            <<<JSON
    {
        "uuid": "$uuidString",
        "values": {
            "sku": [{ "locale": null, "scope": null, "data": "new_identifier" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf('api/rest/v1/products-uuid/%s', $uuid->toString()), [], [], [], $data);

        $expectedContent = [
            'code' => 422,
            'message' => sprintf(
                'The uuid "%s" provided in the request body must match the uuid "%s" provided in the url.',
                $newUuid->toString(),
                $uuid->toString()
            ),
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, \json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateProduct()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {}
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /*
     * TODO To uncomment CPM-698
     */
    public function skipTestUpdateProductWithSameUuid()
    {
        $client = $this->createAuthenticatedClient();
        $uuidString = self::PRODUCT_FAMILY_UUID;

        $data =
            <<<JSON
    {
        "uuid": "$uuidString"
    }
JSON;

        $client->request('PATCH', sprintf('api/rest/v1/products-uuid/%s', $uuidString), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testUpdateProductWithDifferentUuid()
    {
        $client = $this->createAuthenticatedClient();
        $newUuid = Uuid::uuid4();
        $uuidString = $newUuid->toString();

        $data =
            <<<JSON
    {
        "uuid": "$uuidString"
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code' => 422,
            'message' => sprintf(
                'The uuid "%s" provided in the request body must match the uuid "%s" provided in the url.',
                $newUuid->toString(),
                self::PRODUCT_FAMILY_UUID
            ),
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, \json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductCreationWithInvalidValues()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
    {
        "values": {
            "sku": [{ "locale": null, "scope": null, "data": "new_identifier" }],
            "a_metric": {
                "locale": null,
                "scope": null,
                "data": null
            }
        }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "a_metric" expects an array with valid data, one of the values is not an array. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => ['href' => 'http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_'],
            ],
        ];

        $client->request('PATCH', sprintf('api/rest/v1/products-uuid/%s', $uuid->toString()), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductUpdateWithInvalidValues()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {
            "a_metric": {
                "locale": null,
                "scope": null,
                "data": null
            }
        }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "a_metric" expects an array with valid data, one of the values is not an array. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => ['href' => 'http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_'],
            ],
        ];

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductUpdateWithDuplicatedValues()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {
            "a_simple_select": [
                {"locale": null, "scope": null, "data": "optionB"},
                {"locale": null, "scope": null, "data": "optionA"}
            ]
        }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'You cannot update the same product value on the "a_simple_select" attribute twice, with the same scope and locale. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => ['href' => 'http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_'],
            ],
        ];

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_COMPLETE_UUID
        ), [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testProductPartialUpdateWithTheFamilyUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "family": "familyA"
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_family',
            'family'        => 'familyA',
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_family'],
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
        $this->assertSameProducts($expectedProduct, 'product_family');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWithTheFamilyDeleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "family": null
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_family',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_family'],
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
        $this->assertSameProducts($expectedProduct, 'product_family');
    }

    public function testProductPartialUpdateWithTheGroupsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "groups": ["groupB", "groupA"]
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_GROUPS_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_groups',
            'family'        => null,
            'parent'        => null,
            'groups'        => ['groupA', 'groupB'],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_groups'],
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
        $this->assertSameProducts($expectedProduct, 'product_groups');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWithTheGroupsDeleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "groups": [],
        "values": {"sku": [{"locale": null, "scope": null, "data": "product_groups" }]}
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_GROUPS_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_groups',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_groups'],
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
        $this->assertSameProducts($expectedProduct, 'product_groups');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWithTheCategoriesUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "categories": ["categoryA", "categoryA1"],
        "values": {"sku": [{"locale": null, "scope": null, "data": "product_categories" }]}
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => ["categoryA", "categoryA1"],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_categories'],
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
        $this->assertSameProducts($expectedProduct, 'product_categories');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWithTheCategoriesDeleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "parent": null,
        "categories": [],
        "values": {"sku": [{"locale": null, "scope": null, "data": "product_categories" }]}
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_categories'],
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
        $this->assertSameProducts($expectedProduct, 'product_categories');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    /**
     * @group ce
     */
    public function testProductPartialUpdateWithTheAssociationsUpdated()
    {
        $this->createProductModel([
            'code' => 'a_product_model',
            'family_variant' => 'familyVariantA1',
            'values'  => [],
        ]);

        $client = $this->createAuthenticatedClient();

        $uuidProductCategories = self::PRODUCT_CATEGORIES_UUID;
        $uuidProductFamily = self::PRODUCT_FAMILY_UUID;

        $data = <<<JSON
{
    "associations": {
        "PACK": {
            "groups": ["groupA"],
            "products": ["{$uuidProductCategories}", "{$uuidProductFamily}"]
        },
        "SUBSTITUTION": {
            "product_models": ["a_product_model"]
        }
    },
    "values": {"sku": [{"locale": null, "scope": null, "data": "product_associations" }]}
}
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_ASSOCIATIONS_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_associations',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_associations'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => [
                    'groups' => ['groupA'],
                    'product_uuids' => [
                        $uuidProductFamily,
                        $uuidProductCategories,
                    ],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => ['a_product_model']
                ],
                'UPSELL' => [
                    'groups' => [],
                    'product_uuids' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => ['groupA'],
                    'product_uuids' => [$uuidProductCategories],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_associations');

        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWithTheAssociationsDeletedOnGroups()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {"sku": [{"locale": null, "scope": null, "data": "product_associations" }]},
        "associations": {
            "X_SELL": {
                "groups": []
            }
        }
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_ASSOCIATIONS_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_associations',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_associations'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'         => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'UPSELL'       => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'X_SELL'       => ['groups'   => [], 'product_uuids' => [self::PRODUCT_CATEGORIES_UUID], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_associations');
    }

    public function testProductPartialUpdateWithTheAssociationsDeleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {"sku": [{"locale": null, "scope": null, "data": "product_associations" }]},
        "associations": {
            "PACK": {
                "groups": [],
                "products": []
            },
            "X_SELL": {
                "groups": [],
                "products": []
            }
        }
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_ASSOCIATIONS_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_associations',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_associations'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'         => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'UPSELL'       => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'X_SELL'       => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_associations');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    /**
     * @group ce
     */
    public function testProductPartialUpdateWithInvalidAssociatedUuids()
    {
        $client = $this->createAuthenticatedClient();
        $uuid1 = $this->createProduct('associatedProduct1', [])->getUuid()->toString();

        $data = <<<JSON
{
    "associations": {
        "PACK": {
            "groups": ["groupA"],
            "products": ["{$uuid1}", "invalid_uuid"]
        },
        "SUBSTITUTION": {}
    },
    "values": {"sku": [{"locale": null, "scope": null, "data": "product_associations" }]}
}
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_ASSOCIATIONS_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "associations" expects an array with valid data, association format is not valid for the association type "PACK", "product_uuids" expects an array of valid uuids.. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_'
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

    }

    public function testProductPartialUpdateWithProductDisable()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "values": {"sku": [{"locale": null, "scope": null, "data": "product_categories" }]},
        "enabled": false
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => ['master'],
            'enabled'       => false,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_categories'],
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
        $this->assertSameProducts($expectedProduct, 'product_categories');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWhenProductValueAddedOnAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $akeneoJpgPath = $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'));

        $data =
            <<<JSON
    {
        "values": {
            "a_localizable_image": [{
                "locale": "zh_CN",
                "scope": null,
                "data": "${akeneoJpgPath}"
            }],
            "sku": [{"locale": null, "scope": null, "data": "localizable" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_LOCALIZABLE_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'localizable',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'localizable'],
                ],
                'a_localizable_image' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt'],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt'],
                    ['locale' => 'zh_CN', 'scope' => null, 'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt'],
                ]
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'localizable');
        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function testProductPartialUpdateWhenProductValueUpdatedOnAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $ziggyPngPath = $this->getFileInfoKey($this->getFixturePath('ziggy.png'));

        $data =
            <<<JSON
    {
        "values": {
            "a_localizable_image": [{
                "locale": "en_US",
                "scope": null,
                "data": "${ziggyPngPath}"
            }],
            "sku": [{"locale": null, "scope": null, "data": "localizable" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_LOCALIZABLE_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'localizable',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'localizable'],
                ],
                'a_localizable_image' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_ziggy.png'],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt'],
                ]
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'localizable');
    }

    public function testProductPartialUpdateWhenProductValueDeletedOnAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
                {
        "values": {
            "a_localizable_image": [{
                "locale": "en_US",
                "scope": null,
                "data": null
            }],
            "sku": [{"locale": null, "scope": null, "data": "localizable" }]
        }
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_LOCALIZABLE_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'localizable',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'localizable'],
                ],
                'a_localizable_image' => [
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_akeneo.txt'],
                ]
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'localizable');
    }

    public function testProductPartialUpdateOnMultipleAttributes()
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
        "family": "familyA2",
        "categories": ["master", "categoryA"],
        "values": {
            "sku": [{"locale": null, "scope": null, "data": "complete" }],
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

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_COMPLETE_UUID
        ), [], [], [], $data);

        $expectedProduct = [
            'identifier'    => 'complete',
            'family'        => 'familyA2',
            'parent'        => null,
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['categoryA', 'master'],
            'enabled'       => true,
            'values'        => [
                'sku'                                => [
                    ['locale' => null, 'scope' => null, 'data' => 'complete'],
                ],
                'a_date'   => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_simple_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionA'],
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
                    ['locale' => null, 'scope' => null, 'data' => ['airguard', 'braid']],
                ],
                'a_ref_data_simple_select'           => [
                    ['locale' => null, 'scope' => null, 'data' => 'bright-lilac'],
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
            'associations'  => [
                'PACK'         => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'UPSELL'       => ['groups'   => [], 'product_uuids' => [], 'product_models' => []],
                'X_SELL'       => ['groups'   => ['groupA'], 'product_uuids' => [self::PRODUCT_CATEGORIES_UUID], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'complete');
    }

    public function testProductPartialUpdateWithIgnoredProperties()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "created": "2014-06-14T13:12:50+02:00",
        "updated": "2014-06-14T13:12:50+02:00",
        "values": {
         "sku": [{"locale": null, "scope": null, "data": "product_categories" }]
        }
    }
JSON;

        $expectedProduct = [
            'identifier'    => 'product_categories',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],

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
            'quantified_associations' => [],
        ];

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $response = $client->getResponse();


        $this->assertSame('', $response->getContent());
        $this->assertSameProducts($expectedProduct, 'product_categories');
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_categories');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['created']);
        $this->assertNotSame('2014-06-14T13:12:50+02:00', $standardizedProduct['updated']);
    }

    public function testItUpdatesIdentifier()
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

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            sprintf(
                'http://localhost/api/rest/v1/products-uuid/%s',
                $this->getProductUuid('foo')->toString()
            ),
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testCreateProductWithConstraintViolations()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
    {
        "family": "familyA",
        "values": {
            "sku": [{"locale": null, "scope": null, "data": " nji,çsnqj; "}]
        }
    }
JSON;
        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            $uuid->toString()
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' =>  'identifier',
                    'message' => 'This field should not contain any comma or semicolon or leading/trailing space'
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

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_'
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testProductPartialUpdateWithProductDisableWithNullValue()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "enabled": null,
        "sku": [{"locale": null, "scope": null, "data": "product_categories"}]
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_CATEGORIES_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "enabled" expects a boolean as data, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_'
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductPartialUpdateWithAnUnknownAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "family": "familyA2",
        "groups": [],
        "categories": [],
        "values": {
            "sku": [{"locale": null, "scope": null, "data": "product_family"}],
            "unknown_attribute":[{
                "locale": null,
                "scope": null,
                "data": true
            }]
        },
        "associations": {}
    }
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'The unknown_attribute attribute does not exist in your PIM. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => "http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_"
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testProductPartialUpdateWithInvalidFieldData()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "family": ["familyA"],
        "sku": [{"locale": null, "scope": null, "data": "product_family"}]
    }
JSON;
        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            self::PRODUCT_FAMILY_UUID
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "family" expects a string as data, "array" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => "http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_"
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateProductWithInvalidAttributeData()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
    {
        "family": "familyA",
        "values": {
            "sku": [{"locale": null, "scope": null, "data": "big_boot"}],
            "a_text":[{
                "locale": null,
                "scope": null,
                "data": ["an_array"]
            }]
        }
    }
JSON;
        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            $uuid->toString()
        ), [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'The a_text attribute requires a string, a array was detected. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => "http://api.akeneo.com/api-reference.html#patch_products_uuid__uuid_"
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenAssociatingToNonExistingProductModel()
    {
        $client = $this->createAuthenticatedClient();
        $uuid = Uuid::uuid4();

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "big_boot"}]
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
            "href": "http:\/\/api.akeneo.com\/api-reference.html#patch_products_uuid__uuid_"
        }
    }
}
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            $uuid->toString()
        ), [], [], [], $data);

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testAccessDeniedWhenPartialUpdateOnProductWithoutTheAcl()
    {
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_edit');
        $uuid = Uuid::uuid4();

        $data =
            <<<JSON
{
    "values": {
        "sku": [{"scope": null, "locale": null, "data": "foo"}]
    }
}
JSON;

        $client->request('PATCH', sprintf(
            'api/rest/v1/products-uuid/%s',
            $uuid->toString()
        ), [], [], [], $data);
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
