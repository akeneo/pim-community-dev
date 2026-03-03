<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class CreateProductModelWithPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    public function testCreateRootProductModel()
    {
        $this->loader->loadProductsForAssociationPermissions();
        $this->makeAttributeAxesEditable('sub_product_model_axis_attribute');

        $data = <<<JSON
            {
                "code": "root_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["view_category"],
                "parent": null,
                "values": {}
            }
JSON;

        $this->assertCreated($data);

        $expectedProductModel = [
            'code'              => 'root_product_model_creation',
            'family_variant'    => 'family_variant_permission',
            'parent'            => null,
            'categories'        => ['view_category'],
            'values'            => [],
            'created'      => '2016-06-14T13:12:50+02:00',
            'updated'      => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];


        $this->assertSameProductModelWithoutPermission($expectedProductModel, 'root_product_model_creation');
    }

    public function testCreateSubProductModel()
    {
        $this->loader->loadProductsForAssociationPermissions();
        $this->makeAttributeAxesEditable('sub_product_model_axis_attribute');

        $data = <<<JSON
            {
                "code": "sub_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["own_category"],
                "parent": "root_product_model",
                "values": {
                    "sub_product_model_axis_attribute": [
                        {"locale": null, "scope": null, "data": false }
                    ]
                }
            }
JSON;

        $this->assertCreated($data);

        $expectedProductModel = [
            'code'              => 'sub_product_model_creation',
            'family_variant'    => 'family_variant_permission',
            'parent'            => "root_product_model",
            'categories'        => ['own_category'],
            'values'            => [
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => false],
                ]
            ],
            'created'      => '2016-06-14T13:12:50+02:00',
            'updated'      => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];


        $this->assertSameProductModelWithoutPermission($expectedProductModel, 'sub_product_model_creation');
    }

    public function testCreateSubProductModelWithNotVisibleAxisAttribute()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $data = <<<JSON
            {
                "code": "sub_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["own_category"],
                "parent": "root_product_model"
            }
JSON;

        $this->assertValidationFailed($data, "attribute", 'Attribute "sub_product_model_axis_attribute" cannot be empty, as it is defined as an axis for this entity');
    }

    public function testCreateWithoutModifyingViewableAttributeFromParent()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();
        $this->makeAttributeAxesEditable('sub_product_model_axis_attribute');

        $data = <<<JSON
            {
                "code": "sub_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["own_category"],
                "parent": "root_product_model",
                "values": {
                    "root_product_model_view_attribute": [
                        {
                            "locale": "fr_FR",
                            "scope": null,
                            "data": true 
                        }
                    ],
                    "sub_product_model_axis_attribute": [
                        {"locale": null, "scope": null, "data": false }
                    ]
                }
            }
JSON;
        $this->assertCreated($data);
    }

    public function testCreateWithoutModifyingEditableAttributeFromParent()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();
        $this->makeAttributeAxesEditable('sub_product_model_axis_attribute');

        $data = <<<JSON
            {
                "code": "sub_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["own_category"],
                "parent": "root_product_model",
                "values": {
                    "root_product_model_edit_attribute": [
                        {
                            "locale": "en_US",
                            "scope": null,
                            "data": true 
                        }
                    ],
                    "sub_product_model_axis_attribute": [
                        {"locale": null, "scope": null, "data": false }
                    ]
                }
            }
JSON;
        $this->assertCreated($data);
    }

    public function testCreateWithNotViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();
        $this->makeAttributeAxesEditable('sub_product_model_axis_attribute');

        $message = 'Attribute "sub_product_model_edit_attribute" expects an existing and activated locale, "de_DE" given. Check the expected format on the API documentation.';


        $data = <<<JSON
            {
                "code": "sub_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["own_category"],
                "parent": "root_product_model",
                "values": {
                    "sub_product_model_axis_attribute": [
                        {"locale": null, "scope": null, "data": false }
                    ],
                    "sub_product_model_edit_attribute": [
                        {
                            "locale": "de_DE",
                            "scope": null,
                            "data": false
                        }
                    ]
                }
            }
JSON;

        $this->assertUnprocessableEntity($data, $message);
    }

    public function testCreateWithoutModifyingViewableLocaleFromParent()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();
        $this->makeAttributeAxesEditable('sub_product_model_axis_attribute');

        $data = <<<JSON
            {
                "code": "sub_product_model_creation",
                "family_variant": "family_variant_permission",
                "categories": ["own_category"],
                "parent": "root_product_model",
                "values": {
                    "sub_product_model_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ],
                    "root_product_model_edit_attribute": [
                        {
                            "locale": "fr_FR",
                            "scope": null,
                            "data": true 
                        }
                    ]
                }
            }
JSON;

        $this->assertCreated($data);
    }


    public function testResponseWhenSettingNonExistingAttributeAsNumber()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "a_product_model",
    "values": {
      "42": [
        {
          "scope": null,
          "locale": null,
          "data": "a data"
        }
      ]
    }
}
JSON;

        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"42\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http:\/\/api.akeneo.com\/api-reference.html#post_product_models"
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @param string $data data submitted
     */
    private function assertCreated(string $data): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        Assert::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    /**
     * @param string $data
     * @param string $message
     */
    private function assertUnprocessableEntity(string $data, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);
        $response = $client->getResponse();

        $error = <<<JSON
        {
            "code": %s,
            "message": "%s",
            "_links": {
                "documentation":{
                    "href":"http://api.akeneo.com/api-reference.html#post_product_models"
                }
            }
        }
JSON;

        $expected = sprintf($error, Response::HTTP_UNPROCESSABLE_ENTITY, addslashes($message));

        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param string $data
     * @param string $property
     * @param string $message
     */
    private function assertValidationFailed(string $data, string $property, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);
        $response = $client->getResponse();

        $error = <<<JSON
        {
            "code": %s,
            "message": "Validation failed.",
            "errors": [
                {
                    "property": "%s",
                    "message": "%s"
                }
            ]
        }
JSON;

        $expected = sprintf($error, Response::HTTP_UNPROCESSABLE_ENTITY, $property, addslashes($message));

        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array  $expectedProductModel  normalized data of the product that should be created
     * @param string $code                  identifier of the product that should be created
     */
    protected function assertSameProductModelWithoutPermission(array $expectedProductModel, $code)
    {
        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();

        $this->get('doctrine')->getManager()->clear();
        $productModel = $this->get('pim_catalog.repository.product_model_without_permission')->findOneByCode($code);
        $standardizedProductModel = $this->get('pim_standard_format_serializer')->normalize($productModel, 'standard');

        NormalizedProductCleaner::clean($standardizedProductModel);
        NormalizedProductCleaner::clean($expectedProductModel);

        $this->assertSame($expectedProductModel, $standardizedProductModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    /**
     * Creation should not work if attributes as axes are not editable.
     *
     * @param string $code
     */
    private function makeAttributeAxesEditable(string $code)
    {
        $data = [
            'code' => $code,
            'group' => 'attributeGroupA'
        ];

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
