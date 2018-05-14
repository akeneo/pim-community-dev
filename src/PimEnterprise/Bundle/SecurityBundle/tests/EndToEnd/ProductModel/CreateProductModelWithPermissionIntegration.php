<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\EndToEnd\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\SecurityBundle\tests\EndToEnd\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class CreateProductModelWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
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
            'updated'      => '2016-06-14T13:12:50+02:00'
        ];


        $this->assertSameProductModel($expectedProductModel, 'root_product_model_creation');
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
            'updated'      => '2016-06-14T13:12:50+02:00'
        ];


        $this->assertSameProductModel($expectedProductModel, 'sub_product_model_creation');
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
                    "href":"http://api.akeneo.com/api-reference.html#post_product_model"
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
    protected function assertSameProductModel(array $expectedProductModel, $code)
    {
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $productModel = $this->getFromTestContainer('pim_catalog.repository.product_model')->findOneByCode($code);
        $standardizedProductModel = $this->getFromTestContainer('pim_serializer')->normalize($productModel, 'standard');

        NormalizedProductCleaner::clean($standardizedProductModel);
        NormalizedProductCleaner::clean($expectedProductModel);

        $this->assertSame($expectedProductModel, $standardizedProductModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
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

        $attribute = $this->getFromTestContainer('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $this->getFromTestContainer('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->getFromTestContainer('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }
}
