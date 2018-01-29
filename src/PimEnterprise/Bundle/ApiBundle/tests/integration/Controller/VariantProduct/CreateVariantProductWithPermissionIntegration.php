<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\VariantProduct;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class CreateVariantProductWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testCreateVariantProductWithAssociation()
    {
        $this->loader->loadProductModelsForAssociationPermissions();

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "variant_product_axis_attribute": [
                        {"locale": null, "scope": null, "data": false }
                    ]
                },
                "associations": {
                    "X_SELL": {
                        "products": ["product_own"]
                    }
                }
            }
JSON;

        $this->assertCreated($data);

        $expectedProduct = [
            'identifier'   => 'variant_product_creation',
            'family'       => 'family_permission',
            'parent'       => 'sub_product_model',
            'groups'       => [],
            'categories'   => ['own_category'],
            'enabled'      => true,
            'values'       => [
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_axis_attribute'  => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'sku'                                  => [
                    ['locale' => null, 'scope' => null, 'data' => 'variant_product_creation'],
                ],
                'variant_product_axis_attribute'    => [
                    ['locale' => null, 'scope' => null, 'data' => false],
                ],
            ],
            'created'      => '2016-06-14T13:12:50+02:00',
            'updated'      => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK'       => [
                    'groups'   => [],
                    'products' => [],
                ],
                'SUBSTITUTION' => [
                    'groups'   => [],
                    'products' => [],
                ],
                'UPSELL'       => [
                    'groups'   => [],
                    'products' => [],
                ],
                'X_SELL'       => [
                    'groups'   => [],
                    'products' => ['product_own'],
                ],
            ],
        ];


        $this->assertSameProduct($expectedProduct, 'variant_product_creation');
    }

    public function testCreateVariantProductWithNotVisibleAxisAttribute()
    {
        $this->loader->loadProductModelsForAssociationPermissions();
        $this->makeAttributeAxesNotViewable('variant_product_axis_attribute');

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model"
            }
JSON;

        $this->assertValidationFailed($data, "attribute", 'Attribute "variant_product_axis_attribute" cannot be empty, as it is defined as an axis for this entity');
    }

    public function testCreateVariantProductAssociationWithNotViewableProduct()
    {
        $this->loader->loadProductModelsForAssociationPermissions();

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "variant_product_axis_attribute": [
                        {"locale": null, "scope": null, "data": false }
                    ]
                },
                "associations": {
                    "X_SELL": {
                        "products": ["product_no_view"]
                    }
                }
            }
JSON;

        $message = 'You cannot associate a product on which you have not a view permission.';
        $this->assertUnauthorized($data, $message);
    }

    /**
     * @fail
     */
    public function testCreateWithParentProductValueOnNotViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Property "sub_product_model_no_view_attribute" does not exist. Check the expected format on the API documentation.';

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "sub_product_model_no_view_attribute": [
                        {
                            "locale": "en_US",
                            "scope": null,
                            "data": true 
                        }
                    ],
                    "variant_product_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ]
                }
            }
JSON;
        $this->assertUnprocessableEntity($data, $message);
    }

    /**
     * Ignore attributes and locales from product models, without paying attention
     * if it's editable or viewable, or if it has been modified.
     */
    public function testCreateWithParentProductValueOnViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "sub_product_model_view_attribute": [
                        {
                            "locale": "en_US",
                            "scope": null,
                            "data": true 
                        }
                    ],
                    "variant_product_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ]
                }
            }
JSON;
        $this->assertCreated($data);
    }

    public function testCreateWithVariantProductValueOnNotViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "variant_product_edit_attribute" expects an existing and activated locale, "de_DE" given. Check the expected format on the API documentation.';

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "variant_product_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ],
                    "variant_product_edit_attribute": [
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

    /**
     * Ignore attributes and locales from product models, without paying attention
     * if it's editable or viewable, or if it has been modified.
     */
    public function testCreateWithParentProductValueOnNotViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "sub_product_model_edit_attribute" expects an existing and activated locale, "de_DE" given. Check the expected format on the API documentation.';

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "variant_product_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ],
                    "sub_product_model_edit_attribute": [
                        {
                            "locale": "de_DE",
                            "scope": null,
                            "data": true 
                        }
                    ]
                }
            }
JSON;

        $this->assertUnprocessableEntity($data, $message);
    }

    /**
     * Ignore attributes and locales from product models, without paying attention
     * if it's editable or viewable, or if it has been modified.
     */
    public function testCreateWithParentProductValueOnViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "values": {
                    "variant_product_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ],
                    "sub_product_model_edit_attribute": [
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

    public function testCreateVariantProductWithOwnableCategoryFromParent()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = <<<JSON
            {
                "identifier": "variant_product_creation",
                "parent": "sub_product_model",
                "categories": ["view_category"],
                "values": {
                    "variant_product_axis_attribute": [
                        {
                            "locale": null,
                            "scope": null,
                            "data": false
                        }
                    ]
                }
            }
JSON;

        $this->assertCreated($data);
    }

    /**
     * @param string $data
     */
    private function assertCreated(string $data): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        Assert::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    /**
     * @param string $data
     * @param string $message
     */
    private function assertUnauthorized(string $data, string $message): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_FORBIDDEN, addslashes($message));

        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        Assert::assertEquals($expected, $response->getContent());
    }

    /**
     * @param string $data
     * @param string $message
     */
    private function assertUnprocessableEntity(string $data, string $message): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();

        $error = <<<JSON
        {
            "code": "%s",
            "message": "%s",
            "_links": {
                "documentation":{
                    "href":"http://api.akeneo.com/api-reference.html#post_products"
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
    private function assertValidationFailed(string $data, string $property, string $message): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();

        $error = <<<JSON
        {
            "code": "%s",
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
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier      identifier of the product that should be created
     */
    protected function assertSameProduct(array $expectedProduct, $identifier): void
    {
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $product = $this->getFromTestContainer('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $standardizedProduct = $this->getFromTestContainer('pim_serializer')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($standardizedProduct);
        NormalizedProductCleaner::clean($expectedProduct);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * Creation will not work if attributes as axes are not editable.
     * This function allows to avoid this error.
     *
     * @param string $code
     */
    private function makeAttributeAxesNotViewable(string $code): void
    {
        $data = [
            'code' => $code,
            'group' => 'attributeGroupC'
        ];

        $attribute = $this->getFromTestContainer('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $this->getFromTestContainer('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->getFromTestContainer('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }
}
