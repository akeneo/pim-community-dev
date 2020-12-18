<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductModelWithPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    /**
     * @group critical
     */
    public function testUpdateRootProductModelValuesByMergingNonViewableProductValues()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = <<<JSON
{
    "values": {
        "root_product_model_edit_attribute": [
            { "data": false, "locale": "en_US", "scope": null }
        ]
    }
}
JSON;

        $this->assertUpdated('root_product_model', $data);

        $expectedProductModel = [
            'code'          => 'root_product_model',
            'family_variant' => 'family_variant_permission',
            'parent'        => null,
            'categories'    => ['own_category'],
            'values'        => [
                'root_product_model_edit_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => false],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true]
                ],
                'root_product_model_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ]
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $this->assertSameProductWithoutPermission($expectedProductModel, 'root_product_model');
    }

    /**
     * @group critical
     */
    public function testUpdateSubProductModelValuesByMergingNonViewableProductValues()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = '{"values": {"sub_product_model_edit_attribute": [{ "data": false, "locale": "en_US", "scope": null }]}}';

        $this->assertUpdated('sub_product_model', $data);

        $expectedProductModel = [
            'code'           => 'sub_product_model',
            'family_variant' => 'family_variant_permission',
            'parent'         => 'root_product_model',
            'categories'     => ['own_category'],
            'values'         => [
                'root_product_model_edit_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true]
                ],
                'root_product_model_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'sub_product_model_edit_attribute' => [
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => false],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_no_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $this->assertSameProductWithoutPermission($expectedProductModel, 'sub_product_model');
    }

    /**
     * @group critical
     */
    public function testUpdateSubProductModelValuesByMergingNonViewableCategories()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["view_category", "edit_category"]}';

        $sql = <<<SQL
SELECT c.code
FROM pim_catalog_product_model pm
INNER JOIN pim_catalog_category_product_model cpm ON pm.id = cpm.product_model_id
INNER JOIN pim_catalog_category c ON c.id = cpm.category_id
WHERE pm.code = "colored_shoes_own"
SQL;

        $this->assertUpdated('colored_shoes_own', $data, $sql, [
            ['code' => 'category_without_right'],
            ['code' => 'view_category'],
            ['code' => 'edit_category']
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateNotViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/product-models/' . 'colored_sweat_no_view', [], [], [], '{"categories": ["own_category"]}');
        $response = $client->getResponse();

        $expectedContent = sprintf('{"code":%d,"message":"Product model \"colored_sweat_no_view\" does not exist or you do not have permission to access it."}', Response::HTTP_NOT_FOUND);

        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        Assert::assertEquals($expectedContent, $response->getContent());
    }

    /**
     * @group critical
     */
    public function testUpdateOnlyViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Product model "%s" cannot be updated. You only have a view right on this product model.';
        $data = '{"categories": ["own_category"]}';

        $this->assertUnauthorized('colored_shoes_view', $data, sprintf($message, 'colored_shoes_view'));
        $this->assertUnauthorized('colored_tshirt_view', $data, sprintf($message, 'colored_tshirt_view'));
        $this->assertUpdated('colored_tshirt_view', '{}');
    }

    public function testUpdateUnclassifiedProductModelByMergingViewableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["view_category"]}';

        $message = 'You should at least keep your product in one category on which you have an own permission.';
        $this->assertUnauthorized('colored_trousers', $data, $message);
    }

    public function testRemoveViewableCategoriesOnProductEditableButNotOwned()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sweat_edit');
        $this->get('pim_api.updater.product_model')->update($productModel, ['categories' => ['edit_category', 'category_without_right']]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('doctrine.orm.entity_manager')->clear();

        $data = '{"categories": []}';

        $message = 'You should at least keep your product in one category on which you have an own permission.';
        $this->assertUnauthorized('sweat_edit', $data, $message);
    }

    public function testUpdateUnclassifiedProductModelByMergingEditableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["edit_category"]}';

        $this->assertUpdated('colored_trousers', $data);
    }

    public function testUpdateNotViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Property "%s" does not exist. Check the expected format on the API documentation.';

        $data = '{"values": {"root_product_model_no_view_attribute": [{"locale": null, "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'root_product_model_no_view_attribute'));

        $data = '{"values": {"sub_product_model_no_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'sub_product_model_no_view_attribute'));
    }

    public function testUpdateByModifyingViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "%s" belongs to the attribute group "attributeGroupB" on which you only have view permission.';

        $data = '{"values": {"sub_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'sub_product_model_view_attribute'));
    }

    public function testUpdateByModifyingNotViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "%s" expects an existing and activated locale, "de_DE" given. Check the expected format on the API documentation.';

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "de_DE", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'sub_product_model_edit_attribute'));
    }

    public function testUpdateByModifyingViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'You only have a view permission on the locale "fr_FR".';

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'sub_product_model_view_attribute'));
    }

    public function testUpdateOwnOrEditProductModelWithNotViewableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Property "categories" expects a valid category code. The category does not exist, "category_without_right" given. Check the expected format on the API documentation.';

        $data = '{"categories": ["own_category", "category_without_right"]}';
        $this->assertUnprocessableEntity('colored_trousers', $data, $message);
        $this->assertUnprocessableEntity('colored_shoes_own', $data, $message);
        $this->assertUnprocessableEntity('colored_jacket_own', $data, $message);

        $data = '{"categories": ["edit_category", "category_without_right"]}';
        $this->assertUnprocessableEntity('colored_sweat_edit', $data, $message);
        $this->assertUnprocessableEntity('colored_shoes_edit', $data, $message);
    }

    public function testUpdateOwnProductModelWithViewableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["own_category", "view_category"]}';
        $this->assertUpdated('colored_jacket_own', $data);
        $this->assertUpdated('colored_shoes_own', $data);
        $this->assertUpdated('colored_trousers', $data);
    }

    public function testUpdateCategorizedProductModelByKeepingOwnRight()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["own_category"]}';
        $this->assertUpdated('colored_jacket_own', $data);
        $this->assertUpdated('colored_shoes_own', $data);
        $this->assertUpdated('colored_trousers', $data);
    }

    /**
     * @param string $code                            code of the product model
     * @param string $data                            data submitted
     * @param string $sql                             SQL for database query
     * @param array  $expectedProductModelNormalized  expected product model data normalized in standard format
     */
    private function assertUpdated(string $code, string $data, string $sql = null, array $expectedProductModelNormalized = null): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/product-models/' . $code, [], [], [], $data);
        Assert::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        if (null !== $sql) {
            Assert::assertEquals($expectedProductModelNormalized, $this->getDatabaseData($sql));
        }
    }

    /**
     * @param string $code
     * @param string $data
     * @param string $message
     */
    private function assertUnauthorized(string $code, string $data, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/product-models/' . $code, [], [], [], $data);
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_FORBIDDEN, addslashes($message));

        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        Assert::assertEquals($expected, $response->getContent());
    }

    /**
     * @param string $code
     * @param string $data
     * @param string $message
     */
    private function assertUnprocessableEntity(string $code, string $data, string $message = null)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/product-models/' . $code, [], [], [], $data);
        $response = $client->getResponse();

        $error = <<<JSON
        {
            "code": %s,
            "message": "%s",
            "_links": {
                "documentation":{
                    "href":"http://api.akeneo.com/api-reference.html#patch_product_models__code_"
                }
            }
        }
JSON;
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        if(!is_null($message)) {
            $expected = sprintf($error, Response::HTTP_UNPROCESSABLE_ENTITY, addslashes($message));
            Assert::assertJsonStringEqualsJsonString($expected, $response->getContent());
        }
    }

    /**
     * @param array  $expectedProductModel normalized data of the product model that should be created
     * @param string $code      code of the product model that should be created
     */
    protected function assertSameProductWithoutPermission(array $expectedProductModel, $code)
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
