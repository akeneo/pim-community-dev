<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\VariantProduct;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class UpsertVariantProductWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testUpdateVariantProductValuesByMergingNonViewableAssociations()
    {
        $this->loader->loadProductModelsForAssociationPermissions();

        $data = <<<JSON
            {
                "associations": {
                    "X_SELL": {
                        "products": ["product_own"]
                    }
                }
            }
JSON;

        $this->assertUpdated('variant_product', $data);

        $expectedProduct = [
            'identifier'   => 'variant_product',
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
                    ['locale' => null, 'scope' => null, 'data' => 'variant_product'],
                ],
                'variant_product_axis_attribute'    => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
            ],
            'created'      => '2016-06-14T13:12:50+02:00',
            'updated'      => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK'       => [
                    'groups'   => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups'   => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'UPSELL'       => [
                    'groups'   => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'X_SELL'       => [
                    'groups'   => [],
                    'products' => ['product_no_view', 'product_own'],
                    'product_models' => [],
                ],
            ],
        ];


        $this->assertSameProduct($expectedProduct, 'variant_product');
    }

    public function testUpdateVariantProductValuesByMergingNonViewableProductValues()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = <<<JSON
            {
                "values": {
                    "variant_product_edit_attribute": [
                        { "data": false, "locale": "en_US", "scope": null }
                    ]
                }
            }
JSON;

        $this->assertUpdated('variant_product', $data);

        $expectedProduct = [
            'identifier'    => 'variant_product',
            'family'        => 'family_permission',
            'parent'        => 'sub_product_model',
            'groups'        => [],
            'categories'    => ['own_category'],
            'enabled'       => true,
            'values'        => [
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'root_product_model_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'root_product_model_edit_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'sub_product_model_no_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_edit_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'variant_product_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'variant_product_no_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'variant_product_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'variant_product_edit_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => false],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sku'                              => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'variant_product'],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertSameProduct($expectedProduct, 'variant_product');
    }

    public function testUpdateVariantProductValuesByMergingNonViewableCategories()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["view_category", "own_category", "edit_category"]}';

        $sql = <<<SQL
            SELECT c.code
            FROM pim_catalog_product p
            INNER JOIN pim_catalog_category_product cp ON p.id = cp.product_id
            INNER JOIN pim_catalog_category c ON c.id = cp.category_id
            WHERE identifier = "colored_sized_sweat_own"
SQL;


        $this->assertUpdated('colored_sized_sweat_own', $data, $sql, [
            ['code' => 'view_category'],
            ['code' => 'edit_category'],
        ]);

        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $product = $this
            ->getFromTestContainer('pim_catalog.repository.product')
            ->findOneByIdentifier('colored_sized_sweat_own');

        $categories = $product->getCategories();
        $categoryCodes = [];
        foreach ($categories as $category) {
            $categoryCodes[] = $category->getCode();
        }

        Assert::assertEquals(
            ['view_category', 'edit_category', 'own_category', 'category_without_right'],
            $categoryCodes
        );

    }

    public function testUpdateVariantProductAssociationWithNotViewableProduct()
    {
        $this->loader->loadProductModelsForAssociationPermissions();

        $data = <<<JSON
            {
                "associations": {
                    "X_SELL": {
                        "products": ["product_no_view"]
                    }
                }
            }
JSON;

        $message = 'You cannot associate a product on which you have not a view permission.';
        $this->assertUnauthorized('variant_product', $data, $message);
    }

    public function testUpdateNotViewableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'You can neither view, nor update, nor delete the product "colored_sized_sweat_no_view", as it is only categorized in categories on which you do not have a view permission.';
        $data = '{"categories": ["own_category"]}';
        $this->assertUnauthorized('colored_sized_sweat_no_view', $data, $message);
    }

    public function testUpdateOnlyViewableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Product "%s" cannot be updated. It should be at least in an own category.';
        $data = '{"categories": ["own_category"]}';

        $this->assertUnauthorized('colored_sized_shoes_view', $data, sprintf($message, 'colored_sized_shoes_view'));
        $this->assertUnauthorized('colored_sized_tshirt_view', $data, sprintf($message, 'colored_sized_tshirt_view'));
        $this->assertUnauthorized('colored_sized_tshirt_view', $data, sprintf($message, 'colored_sized_tshirt_view'));
        $this->assertUnauthorized('colored_sized_tshirt_view', $data, sprintf($message, 'colored_sized_tshirt_view'));
        $this->assertUpdated('colored_sized_tshirt_view', '{}');
    }

    /**
     * On product values inherited the parents, we only validate attribute and locale visibility.
     * We ignore any modification of the data on product values of the parents.
     */
    public function testUpdateByModifyingProductValueOnNotViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Property "%s" does not exist. Check the expected format on the API documentation.';

        $data = '{"values": {"root_product_model_no_view_attribute": [{"locale": null, "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('variant_product', $data, sprintf($message, 'root_product_model_no_view_attribute'));

        $data = '{"values": {"sub_product_model_no_view_attribute": [{"locale": null, "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('variant_product', $data, sprintf($message, 'sub_product_model_no_view_attribute'));

        $data = '{"values": {"variant_product_no_view_attribute": [{"locale": null, "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('variant_product', $data, sprintf($message, 'variant_product_no_view_attribute'));
    }

    /**
     * On product values inherited the parents, we only validate attribute and locale visibility.
     * We ignore any modification of the data on product values of the parents.
     */
    public function testUpdateByyModifyingProductValueOnViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();


        $data = '{"values": {"root_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUpdated('variant_product', $data);

        $data = '{"values": {"sub_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUpdated('variant_product', $data);

        $message = 'Attribute "%s" belongs to the attribute group "attributeGroupB" on which you only have view permission.';
        $data = '{"values": {"variant_product_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('variant_product', $data, sprintf($message, 'variant_product_view_attribute'));
    }

    /**
     * On product values inherited the parents, we only validate attribute and locale visibility.
     * We ignore any modification of the data on product values of the parents.
     */
    public function testUpdateByModifyingProductValueOnNotViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "%s" expects an existing and activated locale, "de_DE" given. Check the expected format on the API documentation.';

        $data = '{"values": {"root_product_model_edit_attribute": [{"locale": "de_DE", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('variant_product', $data, sprintf($message, 'root_product_model_edit_attribute'));

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "de_DE", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('variant_product', $data, sprintf($message, 'sub_product_model_edit_attribute'));

        $data = '{"values": {"variant_product_edit_attribute": [{"locale": "de_DE", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('variant_product', $data, sprintf($message, 'variant_product_edit_attribute'));
    }

    /**
     * On product values inherited the parents, we only validate attribute and locale visibility.
     * We ignore any modification of the data on product values of the parents.
     */
    public function testUpdateByModifyingProductValueOnViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'You only have a view permission on the locale "fr_FR".';

        $data = '{"values": {"root_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUpdated('variant_product', $data);

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUpdated('variant_product', $data);

        $data = '{"values": {"variant_product_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('variant_product', $data, sprintf($message, 'variant_product_view_attribute'));
    }

    public function testUpdateEditVariantProductWithNotViewableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'You cannot update the field "categories". You should at least own this product to do it.';

        $data = '{"categories": ["edit_category", "category_without_right"]}';
        $this->assertUnauthorized('colored_sized_sweat_edit', $data, $message);
        $this->assertUnauthorized('colored_sized_shoes_edit', $data, $message);
    }

    public function testUpdateOwnVariantProductWithViewableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["own_category", "view_category"]}';
        $this->assertUpdated('colored_sized_sweat_own', $data);
        $this->assertUpdated('colored_sized_shoes_own', $data);
        $this->assertUpdated('colored_sized_trousers', $data);
    }

    public function testUpdateVariantProductByLosingOwnRight()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'You should at least keep your product in one category on which you have an own permission.';
        $data = '{"categories": ["edit_category"]}';
        $this->assertUnauthorized('colored_sized_trousers', $data, $message);
    }

    /**
     * If parent category has own right, product is considered as owned.
     * Therefore, it is successfully updated.
     */
    public function testUpdateCategorizedVariantProductByKeepingOwnRight()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["own_category"]}';
        $this->assertUpdated('colored_sized_trousers', $data);

        $data = '{"categories": ["view_category"]}';
        $this->assertUpdated('colored_sized_sweat_own', $data);
        $this->assertUpdated('colored_sized_shoes_own', $data);

    }

    /**
     * @param string $identifier                code of the product
     * @param string $data                      data submitted
     * @param string $sql                       SQL for database query
     * @param array  $expectedProductNormalized expected product data normalized in standard format
     */
    private function assertUpdated(string $identifier, string $data, string $sql = null, array $expectedProductNormalized = null): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        Assert::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        if (null !== $sql) {
            Assert::assertEquals($expectedProductNormalized, $this->getDatabaseData($sql));
        }
    }

    /**
     * @param string $identifier
     * @param string $data
     * @param string $message
     */
    private function assertUnauthorized(string $identifier, string $data, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_FORBIDDEN, addslashes($message));

        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        Assert::assertEquals($expected, $response->getContent());
    }

    /**
     * @param string $identifier
     * @param string $data
     * @param string $message
     */
    private function assertUnprocessableEntity(string $identifier, string $data, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        $response = $client->getResponse();

        $error = <<<JSON
            {
                "code": "%s",
                "message": "%s",
                "_links": {
                    "documentation":{
                        "href":"http://api.akeneo.com/api-reference.html#patch_products__code_"
                    }
                }
            }
JSON;

        $expected = sprintf($error, Response::HTTP_UNPROCESSABLE_ENTITY, addslashes($message));

        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier      identifier of the product that should be created
     */
    protected function assertSameProduct(array $expectedProduct, $identifier)
    {
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $product = $this->getFromTestContainer('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $standardizedProduct = $this->getFromTestContainer('pim_serializer')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($standardizedProduct);
        NormalizedProductCleaner::clean($expectedProduct);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    protected function getDatabaseData(string $sql): array
    {
        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
