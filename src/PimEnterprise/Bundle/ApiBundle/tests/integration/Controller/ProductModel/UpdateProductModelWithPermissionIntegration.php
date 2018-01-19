<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductModelWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

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
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'root_product_model_view_attribute' => [
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ]
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
        ];

        $this->assertSameProduct($expectedProductModel, 'root_product_model');
    }

    public function testUpdateSubProductModelValuesByMergingNonViewableProductValues()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = '{"values": {"sub_product_model_edit_attribute": [{ "data": false, "locale": "en_US", "scope": null }]}}';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/product-models/sub_product_model', [], [], [], $data);
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

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

    public function testUpdateNotViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'You can neither view, nor update, nor delete the product model "colored_sweat_no_view", as it is only categorized in categories on which you do not have a view permission.';
        $data = '{"categories": ["own_category"]}';
        $this->assertUnauthorized('colored_sweat_no_view', $data, $message);
    }

    public function testUpdateOnlyViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Product model "%s" cannot be updated. It should be at least in an own category.';
        $data = '{"categories": ["own_category"]}';

        $this->assertUnauthorized('colored_shoes_view', $data, sprintf($message, 'colored_shoes_view'));
        $this->assertUnauthorized('colored_tshirt_view', $data, sprintf($message, 'colored_tshirt_view'));
        $this->assertUpdated('colored_tshirt_view', '{}');
    }

    /**
     * @fail
     */
    public function testUpdateNotViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Property "%s" does not exist. Check the expected format on the API documentation.';

        $data = '{"values": {"root_product_model_no_view_attribute": [{"locale": null, "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'root_product_model_no_view_attribute'));

        $data = '{"values": {"sub_product_model_no_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'sub_product_model_no_view_attribute'));
    }

    /**
     * @fail
     */
    public function testUpdateByModifyingViewableAttribute()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "%s" belongs to the attribute group "attributeGroupB" on which you only have view permission.';

        $data = '{"values": {"root_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'root_product_model_view_attribute'));

        $data = '{"values": {"sub_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'sub_product_model_view_attribute'));
    }

    /**
     * @fail
     */
    public function testUpdateAxesAttributeFail()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "%s" cannot be empty, as it is defined as an axis for this entity';

        $data = '{"values": {"root_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'sub_product_model_view_attribute'));

        $data = '{"values": {"sub_product_model_view_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnprocessableEntity('sub_product_model', $data, sprintf($message, 'sub_product_model_view_attribute'));
    }

    /**
     * @fail
     */
    public function testUpdateByModifyingNotViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'Attribute "%s" expects an existing and activated locale, "de_DE" given. Check the expected format on the API documentation.';

        $data = '{"values": {"root_product_model_edit_attribute": [{"locale": "de_DE", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'root_product_model_edit_attribute'));

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "de_DE", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'sub_product_model_edit_attribute'));
    }

    /**
     * @fail
     */
    public function testUpdateByModifyingViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $message = 'You only have a view permission on the locale "fr_FR".';

        $data = '{"values": {"root_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'root_product_model_view_attribute'));

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":false}]}}';
        $this->assertUnauthorized('sub_product_model', $data, sprintf($message, 'sub_product_model_view_attribute'));
    }

    /**
     * @fail
     */
    public function testUpdateWithoutModifyingViewableLocale()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = '{"values": {"root_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":true}]}}';
        $this->assertUpdated('sub_product_model', $data);

        $data = '{"values": {"sub_product_model_edit_attribute": [{"locale": "fr_FR", "scope":null, "data":true}]}}';
        $this->assertUpdated('sub_product_model', $data);
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

    /**
     * @fail
     */
    public function testUpdateOwnProductModelWithViewableCategory()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["own_category", "view_category"]}';
        $this->assertUpdated('colored_jacket_own', $data);
        $this->assertUpdated('colored_shoes_own', $data);
        $this->assertUpdated('colored_trousers', $data);
    }

    /**
     * @fail
     */
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
    private function assertUnprocessableEntity(string $code, string $data, string $message)
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
        $expected = sprintf($error, Response::HTTP_UNPROCESSABLE_ENTITY, addslashes($message));
        if(isset(json_decode($response->getContent(), true)['errors'])) {
            var_dump(json_decode($response->getContent(), true)['errors']);
        }
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array  $expectedProductModel normalized data of the product model that should be created
     * @param string $code      code of the product model that should be created
     */
    protected function assertSameProduct(array $expectedProductModel, $code)
    {
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $productModel = $this->getFromTestContainer('pim_catalog.repository.product_model')->findOneByCode($code);
        $standardizedProductModel = $this->getFromTestContainer('pim_serializer')->normalize($productModel, 'standard');

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
