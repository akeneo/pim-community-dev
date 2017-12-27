<?php

namespace Pim\Component\Catalog\tests\integration\EntityWithFamily;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Akeneo\Test\IntegrationTestsBundle\Assertion;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test the variant product creation Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct
 */
final class CreateVariantProductFromProductIntegration extends TestCase
{
    /** @var ProductInterface */
    private $product;

    /** @var ProductModelInterface */
    private $productModel;

    /**
     * Caution: here we need a valid product and product model. We need to save them in the database because our models
     * are coupled to the database (id, updated, created). We don't use our saver here because we don't want to
     * dispatch event, we just want to save data in the database. So no more problem with indexation.
     *
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->product = $this->getFromTestContainer('akeneo_integration_tests.catalog.factory.product')->create(
            'my-product',
            'clothing',
            [
                // Root product model
                'description' => [['data' => 'description', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                'name' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
                'meta_description' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
                'meta_title' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
                // Sub product model
                'color' => [['data' => 'red', 'locale' => null, 'scope' => null]],
                'composition' => [['data' => 'name', 'locale' => null, 'scope' => null]],
                // The variant product must only have the following values.
                'size' => [['data' => 'l', 'locale' => null, 'scope' => null]],
                'ean' => [['data' => 'ean', 'locale' => null, 'scope' => null]],
                'sku' => [['data' => 'sky', 'locale' => null, 'scope' => null]]
            ],
            ['master_accessories_belts'],
            ['related'],
            ['X_SELL' => ['products' => ['1111111113']]]
        );

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save($this->product);

        $rootProductModel = $this->getFromTestContainer('akeneo_integration_tests.catalog.factory.product_model')->create(
            'my-product-model',
            'clothing_color_size',
            [
                'description' => [['data' => 'description', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                'name' => [['data' => 'name', 'locale' => 'en_US', 'scope' => null]],
            ],
            '',
            ['master_accessories']
        );

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save($rootProductModel);

        $this->productModel = $this->getFromTestContainer('akeneo_integration_tests.catalog.factory.product_model')->create(
            'my-sub-product-model',
            'clothing_color_size',
            [
                'color' => [['data' => 'red', 'locale' => null, 'scope' => null]],
                'composition' => [['data' => 'name', 'locale' => null, 'scope' => null]],
            ],
            $rootProductModel->getCode(),
            ['master_accessories']
        );

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save($this->productModel);
    }

    public function test_that_the_variant_product_have_the_same_properties_than_the_product()
    {
        $variantProduct = $this->get('pim_catalog.entity_with_family.create_variant_product_from_product')
            ->from($this->product, $this->productModel);

        $this->assertSame($this->productModel, $variantProduct->getParent());
        $this->assertSame('clothing_color_size', $variantProduct->getFamilyVariant()->getCode());
        $this->assertSame($this->product->getCompletenesses(), $variantProduct->getCompletenesses());
        $this->assertSame($this->product->getAssociations(), $variantProduct->getAssociations());
        $this->assertSame($this->product->getCreated(), $variantProduct->getCreated());
        $this->assertSame($this->product->getUpdated(), $variantProduct->getUpdated());
        $this->assertSame($this->product->isEnabled(), $variantProduct->isEnabled());
        $this->assertSame($this->product->getIdentifier(), $variantProduct->getIdentifier());
        $this->assertSame($this->productModel->getFamilyVariant(), $variantProduct->getFamilyVariant());

        // IMPORTANT: we're not assigning categories and groups, because doing so would delete the association!
        // @see AddParentAProductSubscriber and PIM-7088
        $this->assertEquals($variantProduct->getGroups(), new ArrayCollection());
        $this->assertEquals($variantProduct->getCategoriesForVariation(), new ArrayCollection());
    }

    public function test_that_the_variant_product_have_product_values_without_product_model_values()
    {
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
