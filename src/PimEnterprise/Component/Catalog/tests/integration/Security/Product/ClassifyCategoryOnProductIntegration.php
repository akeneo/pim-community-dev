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
class ClassifyCategoryOnProductIntegration extends AbstractSecurityTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->saveProduct('product_a', [
            'categories' => ['master', 'categoryA', 'categoryA1', 'categoryB'],
        ]);
    }

    public function testAddNewCategoryOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $categories = ['categoryA', 'categoryA1', 'categoryA2', 'master'];
        $this->updateProduct($product, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories(['master', 'categoryA', 'categoryA1', 'categoryA2', 'categoryB'], $this->getCategoriesFromDatabase('product_a'));
    }

    public function testDeleteCategoryOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $categories = ['categoryA1', 'categoryA2', 'master'];
        $this->updateProduct($product, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories(['master', 'categoryA1', 'categoryA2', 'categoryB'], $this->getCategoriesFromDatabase('product_a'));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You should at least keep your product in one category on which you have an own permission.
     */
    public function testFailToRemoveOwnPermissionOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'categories' => []
        ]);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    public function testDeleteAllCategoriesOnProduct()
    {
        $this->generateToken('julia');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'categories' => []
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame($product->getCategoryCodes(), []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testFailedToCreateAProductWithCategoryNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['categories' => ['categoryB']]);
    }

    public function testCreateAProductWithCategoryViewable()
    {
        $this->generateToken('mary');
        $categories = ['categoryA2'];
        $product = $this->createProduct('product', ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testCreateAProductWithCategoryEditable()
    {
        $this->generateToken('mary');
        $categories = ['categoryA'];
        $product = $this->createProduct('product', ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testCreateAProductWithOwnCategory()
    {
        $this->generateToken('mary');
        $categories = ['master'];
        $product = $this->createProduct('product', ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testFailedToUpdateAProductWithCategoryNotViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['categoryB']]);
    }

    public function testUpdateAProductWithCategoryViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $categories = ['categoryA2', 'master'];
        $this->updateProduct($product, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testUpdateAProductWithCategoryEditable()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $categories = ['categoryA', 'master'];
        $this->updateProduct($product, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testUpdateAProductWithOwnCategory()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $categories = ['master', 'master_china'];
        $this->updateProduct($product, ['categories' => $categories]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product "product" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateACategoryOnAProductOnlyViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['master']]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product "product" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateGroupsOnAProductOnlyViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['groups' => ['groupA']]);
    }
    
    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product "product" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateEnabledOnAProductOnlyViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['enabled' => false]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product "product" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateFamilyOnAProductOnlyViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['family' => 'familyA']);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product "product" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateAssociationsOnAProductOnlyViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['associations' => [
            'X_SELL' => [
                'products' => ['product_a']
            ]
        ]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Product "product" cannot be updated. It should be at least in an own category
     */
    public function testFailToUpdateValuesOnAProductOnlyViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => [
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
