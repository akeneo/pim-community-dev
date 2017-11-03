<?php

namespace Pim\Component\Catalog\tests\integration\EntityWithFamily;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Akeneo\Test\IntegrationTestsBundle\Assertion;

/**
 * Test the variant product creation Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct
 */
class CreateVariantProductFromProductIntegration extends TestCase
{
    /** @var ProductInterface */
    private $product;

    /** @var ProductModelInterface */
    private $productModel;

    /**
     * Caution: here we need a valid product and product model. We need to save them in the database because our models
     * are coupled to the database (id, updated, created). We don't use our saver here because we don't want to
     * dispatch event, we just want to save data in the database. So no more problem with indexation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->product = $this->getFromTestContainer('akeneo_integration_tests.catalog.factory.product')->create(
            'my-product',
            'accessories',
            [
                'color' => [['data' => 'red', 'locale' => null, 'scope' => null]],
                'description' => [['data' => 'description', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                'name' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
                'meta_description' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
                'meta_title' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
                // The variant product must only have the following values.
                'size' => [['data' => 'l', 'locale' => null, 'scope' => null]],
                'ean' => [['data' => 'ean', 'locale' => null, 'scope' => null]],
                'sku' => [['data' => 'sky', 'locale' => null, 'scope' => null]]
            ],
            ['master_accessories_belts'],
            ['related'],
            ['X_SELL' => ['products' => ['1111111171']]]
        );

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save($this->product);

        $this->productModel = $this->getFromTestContainer('akeneo_integration_tests.catalog.factory.product_model')->create(
            'my-product-model',
            'accessories_size',
            [
                'color' => [['data' => 'red', 'locale' => null, 'scope' => null]],
                'description' => [['data' => 'description', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                'name' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
            ],
            '',
            ['master_accessories']
        );

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save($this->productModel);
    }

    public function test that the variant product have the same properties than the product()
    {
        /** @var VariantProductInterface $variantProduct */
        $variantProduct = $this->get('pim_catalog.entity_with_family.create_variant_product_from_product')
            ->from($this->product, $this->productModel);

        $this->assertSame($this->productModel, $variantProduct->getParent());
        $this->assertSame($this->product->getGroups(), $variantProduct->getGroups());
        $this->assertSame($this->product->getCompletenesses(), $variantProduct->getCompletenesses());
        $this->assertSame($this->product->getAssociations(), $variantProduct->getAssociations());
        $this->assertSame($this->product->getCreated(), $variantProduct->getCreated());
        $this->assertSame($this->product->getUpdated(), $variantProduct->getUpdated());
        $this->assertSame($this->product->isEnabled(), $variantProduct->isEnabled());
        $this->assertSame($this->product->getIdentifier(), $variantProduct->getIdentifier());
        $this->assertSame($this->productModel->getFamilyVariant(), $variantProduct->getFamilyVariant());

        $assertCollection = new Assertion\Collection($this->product->getCategories(), $variantProduct->getCategories());
        $assertCollection->hasFollowingEntities();
    }

    public function test that the variant product have product values without product model values()
    {
        /** @var VariantProductInterface $variantProduct */
        $variantProduct = $this->get('pim_catalog.entity_with_family.create_variant_product_from_product')
            ->from($this->product, $this->productModel);

        $assertValues = new Assertion\ValuesCollection(['size', 'ean', 'sku'], $variantProduct->getValuesForVariation());
        $assertValues->hasSameValues();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
