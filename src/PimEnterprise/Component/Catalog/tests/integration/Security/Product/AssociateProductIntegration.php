<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security\Product;

use Pim\Component\Catalog\Model\ProductInterface;
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

        $this->assertSame($this->getRawAssociations($product), [
            'X_SELL' => [
                'products' => ['product_visible_a', 'product_visible_b', 'product_visible_c', 'product_not_visible_by_redactor'],
                'groups' => ['groupA', 'groupB']
            ]
        ]);
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

        $this->assertSame($this->getRawAssociations($product), [
            'X_SELL' => [
                'products' => ['product_visible_a', 'product_not_visible_by_redactor'],
                'groups' => ['groupA', 'groupB']
            ]
        ]);
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

        $this->assertSame($this->getRawAssociations($product), [
            'X_SELL' => [
                'products' => ['product_not_visible_by_redactor'],
                'groups' => ['groupA', 'groupB']
            ]
        ]);
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

        $this->assertSame($this->getRawAssociations($product), [
            'X_SELL' => [
                'products' => ['product_visible_a', 'product_not_visible_by_redactor', 'product_visible_b'],
                'groups' => ['groupA', 'groupB']
            ],
            'UPSELL' => [
                'products' => ['product_visible_a']
            ]
        ]);
    }

    private function getRawAssociations(ProductInterface $product): array
    {
        $rawAssociations = [];

        foreach ($product->getAssociations() as $association) {
            $associationTypeCode = $association->getAssociationType()->getCode();

            foreach ($association->getProducts() as $associatedProduct) {
                $rawAssociations[$associationTypeCode]['products'][] = $associatedProduct->getIdentifier();
            }
            foreach ($association->getGroups() as $associatedGroup) {
                $rawAssociations[$associationTypeCode]['groups'][] = $associatedGroup->getCode();
            }
        }

        return $rawAssociations;
    }
}
