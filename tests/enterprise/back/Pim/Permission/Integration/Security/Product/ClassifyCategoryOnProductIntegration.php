<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Security\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
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

        $this->createOrUpdateProduct('product_a', [new SetCategories(['master', 'categoryA', 'categoryA1', 'categoryB'])]);
    }

    public function testAddNewCategoryOnProduct()
    {
        $categories = ['categoryA', 'categoryA1', 'categoryA2', 'master'];
        $this->createOrUpdateProduct('product_a', [new SetCategories($categories)], 'mary');

        $this->assertCategories(['master', 'categoryA', 'categoryA1', 'categoryA2', 'categoryB'], $this->getCategoriesFromDatabase('product_a'));
    }

    public function testDeleteCategoryOnProduct()
    {
        $categories = ['categoryA1', 'categoryA2', 'master'];
        $this->createOrUpdateProduct('product_a', [new SetCategories($categories)], 'mary');

        $this->assertCategories(['master', 'categoryA1', 'categoryA2', 'categoryB'], $this->getCategoriesFromDatabase('product_a'));
    }

    public function testFailToRemoveOwnPermissionOnProduct()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You should at least keep your product in one category on which you have an own permission');

        $this->createOrUpdateProduct('product_a', [new SetCategories([])], 'mary');
    }

    public function testDeleteAllCategoriesOnProduct()
    {
        $product = $this->createOrUpdateProduct('product_a', [new SetCategories([])], 'julia');

        $this->assertSame($product->getCategoryCodes(), []);
    }

    public function testFailedToCreateAProductWithCategoryNotViewable()
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "categoryB" category does not exist');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryB'])], 'mary');
    }

    public function testCreateAProductWithCategoryViewable()
    {
        $categories = ['categoryA2'];
        $this->createOrUpdateProduct('product', [new SetCategories($categories)], 'mary');

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testCreateAProductWithCategoryEditable()
    {
        $categories = ['categoryA'];
        $product = $this->createOrUpdateProduct('product', [new SetCategories($categories)], 'mary');

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testCreateAProductWithOwnCategory()
    {
        $categories = ['master'];
        $this->createOrUpdateProduct('product', [new SetCategories($categories)], 'mary');

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testFailedToUpdateAProductWithCategoryNotViewable()
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "categoryB" category does not exist');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryB'])], 'mary');
    }

    public function testUpdateAProductWithCategoryViewable()
    {
        $this->createOrUpdateProduct('product', [new SetCategories(['master'])]);

        $categories = ['categoryA2', 'master'];
        $this->createOrUpdateProduct('product', [new SetCategories($categories)], 'mary');

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testUpdateAProductWithCategoryEditable()
    {
        $this->createOrUpdateProduct('product', [new SetCategories(['master'])]);

        $categories = ['categoryA', 'master'];
        $this->createOrUpdateProduct('product', [new SetCategories($categories)], 'mary');

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testUpdateAProductWithOwnCategory()
    {
        $this->createOrUpdateProduct('product', [new SetCategories(['master'])]);

        $categories = ['master', 'master_china'];
        $this->createOrUpdateProduct('product', [new SetCategories($categories)], 'mary');

        $this->assertCategories($categories, $this->getCategoriesFromDatabase('product'));
    }

    public function testFailToUpdateACategoryOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. You only have a view right on this product.');
        $this->createOrUpdateProduct('product', [new SetCategories(['categoryA1'])]);

        $this->createOrUpdateProduct('product', [new SetCategories(['master'])], 'mary');
    }

    public function testFailToUpdateGroupsOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. You only have a view right on this product.');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryA1'])]);

        $this->createOrUpdateProduct('product', [new SetGroups(['groupA'])], 'mary');
    }

    public function testFailToUpdateEnabledOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. You only have a view right on this product.');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryA1'])]);

        $this->createOrUpdateProduct('product', [new SetEnabled(false)], 'mary');
    }

    public function testFailToUpdateFamilyOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. You only have a view right on this product.');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryA1'])]);

        $this->createOrUpdateProduct('product', [new SetFamily('familyA')], 'mary');
    }

    public function testFailToUpdateAssociationsOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. You only have a view right on this product.');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryA1'])]);

        $this->createOrUpdateProduct('product', [new AssociateProducts('X_SELL', ['product_a'])], 'mary');
    }

    public function testFailToUpdateValuesOnAProductOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Product "product" cannot be updated. You only have a view right on this product.');

        $this->createOrUpdateProduct('product', [new SetCategories(['categoryA1'])]);

        $this->createOrUpdateProduct('product', [
            new SetTextValue('a_text', null, null, 'data')
        ], 'mary');
    }

    private function assertCategories(array $expected, array $result): void
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
