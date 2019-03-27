<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Security\Product;

use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTestEnterprise\Pim\Permission\Integration\Security\AbstractSecurityTestCase;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

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
    protected function setUp(): void
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

    public function testFailToRemoveOwnPermissionOnProduct()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You should at least keep your product in one category on which you have an own permission.');

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

    public function testFailedToCreateAProductWithCategoryNotViewable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "categories" expects a valid category code. The category does not exist, "categoryB" given.');

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

    public function testFailedToUpdateAProductWithCategoryNotViewable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "categories" expects a valid category code. The category does not exist, "categoryB" given.');

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

    public function testFailToUpdateACategoryOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. It should be at least in an own category');

        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['master']]);
    }

    public function testFailToUpdateGroupsOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. It should be at least in an own category');

        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['groups' => ['groupA']]);
    }

    public function testFailToUpdateEnabledOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. It should be at least in an own category');

        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['enabled' => false]);
    }

    public function testFailToUpdateFamilyOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. It should be at least in an own category');

        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['family' => 'familyA']);
    }

    public function testFailToUpdateAssociationsOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. It should be at least in an own category');

        $product = $this->saveProduct('product', ['categories' => ['categoryA1']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['associations' => [
            'X_SELL' => [
                'products' => ['product_a']
            ]
        ]]);
    }

    public function testFailToUpdateValuesOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. It should be at least in an own category');

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
