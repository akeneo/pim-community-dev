<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security\Product;

use PimEnterprise\Component\Catalog\tests\integration\Security\AbstractSecurityTestCase;

/**
 * +----------+--------------------------------------------------------------------------+
 * |          |                             Categories                                   |
 * +  Roles   +--------------------------------------------------------------------------+
 * |          |     master    |   categoryA   |  categoryA1 / categoryA2 |   categoryB   |
 * +----------+--------------------------------------------------------------------------+
 * | Redactor | View,Edit,Own |   View,Edit   |            View          |       -       |
 * | Manager  | View,Edit,Own | View,Edit,Own |       View,Edit,Own      | View,Edit,Own |
 * +----------+--------------------------------------------------------------------------+
 */
class ClassifyCategoryOnProductModelIntegration extends AbstractSecurityTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->saveProductModel('product_model_a', [
            'categories' => ['master', 'categoryA', 'categoryA1', 'categoryB'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You should at least keep your product in one category on which you have an own permission.
     */
    public function testFailToRemoveOwnPermissionOnProductModel()
    {
        $this->generateToken('mary');
        $productModel = $this->getProductModel('product_model_a');

        $this->updateProductModel($productModel, [
            'categories' => []
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    public function testDeleteAllCategoriesOnProductModel()
    {
        $this->generateToken('julia');
        $productModel = $this->getProductModel('product_model_a');

        $this->updateProductModel($productModel, [
            'categories' => []
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertSame($productModel->getCategoryCodes(), []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testFailedToCreateAProductModelWithCategoryNotViewable()
    {
        $this->generateToken('mary');
        $this->createProductModel('product_model', [
            'categories' => ['categoryB'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
    }

    public function testCreateAProductModelWithCategoryViewable()
    {
        $this->generateToken('mary');
        $categories = ['categoryA2'];
        $productModel = $this->createProductModel('product_model', [
            'categories' => $categories,
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertCategories($categories, $this->getCategoriesFromDatabaseForProductModel('product_model'));
    }

    public function testCreateAProductModelWithCategoryEditable()
    {
        $this->generateToken('mary');
        $categories = ['categoryA'];
        $productModel = $this->createProductModel('product_model', [
            'categories' => $categories,
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertCategories($categories, $this->getCategoriesFromDatabaseForProductModel('product_model'));
    }

    public function testCreateAProductModelWithOwnCategory()
    {
        $this->generateToken('mary');
        $categories = ['master'];
        $productModel = $this->createProductModel('product_model', [
            'categories' => $categories,
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertCategories($categories, $this->getCategoriesFromDatabaseForProductModel('product_model'));
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testFailedToUpdateAProductModelWithCategoryNotViewable()
    {
        $productModel = $this->saveProductModel('product_model', [
            'categories' => ['master'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->generateToken('mary');

        $this->updateProductModel($productModel, ['categories' => ['categoryB']]);
    }

    public function testUpdateAProductModelWithCategoryViewable()
    {
        $productModel = $this->saveProductModel('product_model', [
            'categories' => ['master'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->generateToken('mary');

        $categories = ['categoryA2', 'master'];
        $this->updateProductModel($productModel, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertCategories($categories, $this->getCategoriesFromDatabaseForProductModel('product_model'));
    }

    public function testUpdateAProductModelWithCategoryEditable()
    {
        $productModel = $this->saveProductModel('product_model', [
            'categories' => ['master'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->generateToken('mary');

        $categories = ['categoryA', 'master'];
        $this->updateProductModel($productModel, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertCategories($categories, $this->getCategoriesFromDatabaseForProductModel('product_model'));
    }

    public function testUpdateAProductModelWithOwnCategory()
    {
        $productModel = $this->saveProductModel('product_model', [
            'categories' => ['master'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->generateToken('mary');

        $categories = ['master', 'master_china'];
        $this->updateProductModel($productModel, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->assertCategories($categories, $this->getCategoriesFromDatabaseForProductModel('product_model'));
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product model "product_model" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateACategoryOnAProductModelOnlyViewable()
    {
        $productModel = $this->saveProductModel('product_model', [
            'categories' => ['categoryA1'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->generateToken('mary');

        $this->updateProductModel($productModel, ['categories' => ['master']]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product model "product_model" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateValuesOnAProductModelOnlyViewable()
    {
        $productModel = $this->saveProductModel('product_model', [
            'categories' => ['categoryA1'],
            'family_variant' => 'familyVariantA1',
            'parent' => null
        ]);
        $this->generateToken('mary');

        $this->updateProductModel($productModel, ['values' => [
            'a_text' => [['data' => 'data', 'locale' => null, 'scope' => null]]
        ]]);
    }

    /**
     * @param array $expected
     * @param array $result
     */
    private function assertCategories(array $expected, array $result)
    {
        $categories = [];
        sort($expected);
        sort($result);

        foreach ($expected as $category) {
            $categories[] = ['code' => $category];
        }

        $this->assertSame($categories, $result);
    }
}
