<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Security\Product;

use AkeneoTestEnterprise\Pim\Permission\Integration\Security\AbstractSecurityTestCase;

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
class AssociateProductIntegration extends AbstractSecurityTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->saveProduct('product_visible_a', ['categories' => ['categoryA1']]);
        $this->saveProduct('product_not_visible_by_redactor', ['categories' => ['categoryB']]);
        $this->saveProduct('product_visible_b', ['categories' => ['categoryA']]);
        $this->saveProduct('product_visible_c', ['categories' => ['categoryA']]);

        $this->saveProduct('product', [
            'categories' => ['master', 'categoryA', 'categoryA1', 'categoryB'],
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_visible_a', 'product_not_visible_by_redactor', 'product_visible_b'],
                    'groups' => ['groupA', 'groupB']
                ]
            ]
        ]);
    }

    public function testAddNewAssociatedProductOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product');

        $this->updateProduct($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_visible_a', 'product_visible_b', 'product_visible_c'],
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame([
            ['code' => 'X_SELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_b'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_c'],
        ], $this->getAssociationFromDatabase('product'));
    }

    public function testDeleteAssociatedProductOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product');

        $this->updateProduct($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_visible_a'],
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame([
            ['code' => 'X_SELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
        ], $this->getAssociationFromDatabase('product'));
    }

    public function testDeleteAllAssociatedProductOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product');

        $this->updateProduct($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => [],
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame([
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
        ], $this->getAssociationFromDatabase('product'));
    }

    public function testAddAssociatedProductOnNewAssociation()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product');

        $this->updateProduct($product, [
            'associations' => [
                'UPSELL' => [
                    'products' => ['product_visible_a'],
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame([
            ['code' => 'UPSELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_b'],
        ], $this->getAssociationFromDatabase('product'));
    }
}
