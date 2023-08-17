<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Security\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->createOrUpdateProduct('product_visible_a', [new SetCategories(['categoryA1'])]);
        $this->createOrUpdateProduct('product_not_visible_by_redactor', [new SetCategories(['categoryB'])]);
        $this->createOrUpdateProduct('product_visible_b', [new SetCategories(['categoryA'])]);
        $this->createOrUpdateProduct('product_visible_c', [new SetCategories(['categoryA'])]);

        $this->createOrUpdateProduct('product', [
            new SetCategories(['master', 'categoryA', 'categoryA1', 'categoryB']),
            new AssociateProducts('X_SELL', ['product_visible_a', 'product_not_visible_by_redactor', 'product_visible_b']),
            new AssociateGroups('X_SELL', ['groupA', 'groupB']),
        ]);
    }

    public function testAddNewAssociatedProductOnProduct()
    {
        $this->createOrUpdateProduct('product', [
            new ReplaceAssociatedProducts('X_SELL', ['product_visible_a', 'product_visible_b', 'product_visible_c'])
        ], 'mary');

        $this->assertSame([
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_b'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_c'],
        ], $this->getAssociationFromDatabase('product'));
    }

    public function testDeleteAssociatedProductOnProduct()
    {
        $this->createOrUpdateProduct('product', [
            new ReplaceAssociatedProducts('X_SELL', ['product_visible_a']),
        ], 'mary');

        $this->assertSame([
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_a'],
        ], $this->getAssociationFromDatabase('product'));
    }

    public function testDeleteAllAssociatedProductOnProduct()
    {
        $this->createOrUpdateProduct('product', [
            new ReplaceAssociatedProducts('X_SELL', []),
        ], 'mary');

        $this->assertSame([
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
        ], $this->getAssociationFromDatabase('product'));
    }

    public function testAddAssociatedProductOnNewAssociation()
    {
        $this->createOrUpdateProduct('product', [
            new ReplaceAssociatedProducts('UPSELL', ['product_visible_a'])
        ], 'mary');

        $this->assertSame([
            ['code' => 'UPSELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_not_visible_by_redactor'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_a'],
            ['code' => 'X_SELL', 'identifier' => 'product_visible_b'],
        ], $this->getAssociationFromDatabase('product'));
    }
}
