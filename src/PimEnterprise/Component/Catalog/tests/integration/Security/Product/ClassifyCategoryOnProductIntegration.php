<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security\Product;

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

        $this->updateProduct($product, [
            'categories' => ['master', 'categoryA', 'categoryA1', 'categoryA2']
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame($product->getCategoryCodes(), ['categoryA', 'categoryA1', 'categoryA2', 'categoryB', 'master']);
    }

    public function testDeleteCategoryOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'categories' => ['master', 'categoryA1', 'categoryA2']
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame($product->getCategoryCodes(), ['categoryA1', 'categoryA2', 'categoryB', 'master']);
    }

    public function testDeleteAllCategoryOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'categories' => []
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->assertSame($product->getCategoryCodes(), ['categoryB']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testCreateAProductWithCategoryNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['categories' => ['categoryB']]);
    }

    public function testCreateAProductWithCategoryViewable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['categories' => ['categoryA2']]);

        $this->assertSame($product->getCategoryCodes(), ['categoryA2']);
    }

    public function testCreateAProductWithCategoryEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['categories' => ['categoryA']]);

        $this->assertSame($product->getCategoryCodes(), ['categoryA']);
    }

    public function testCreateAProductWithOwnCategory()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['categories' => ['master']]);

        $this->assertSame($product->getCategoryCodes(), ['master']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testUpdateAProductWithCategoryNotViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA2']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['categoryB']]);
    }

    public function testUpdateAProductWithCategoryViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['categoryA2', 'master']]);
        $this->assertSame($product->getCategoryCodes(), ['categoryA2', 'master']);
    }

    public function testUpdateAProductWithCategoryEditable()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['categoryA', 'master']]);
        $this->assertSame($product->getCategoryCodes(), ['categoryA', 'master']);
    }

    public function testUpdateAProductWithOwnCategory()
    {
        $product = $this->saveProduct('product', ['categories' => ['master']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['master_china', 'master']]);
        $this->assertSame($product->getCategoryCodes(), ['master', 'master_china']);
    }
}
