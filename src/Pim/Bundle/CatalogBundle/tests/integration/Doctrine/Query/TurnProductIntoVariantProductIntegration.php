<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Query;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Akeneo\Test\IntegrationTestsBundle\Assertion;

class TurnProductIntoVariantProductIntegration extends TestCase
{
    /** @var ProductInterface */
    private $product;

    /** @var ProductModelInterface */
    private $productModel;

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
                // The variant product must only have the following values.
                'size' => [['data' => 'l', 'locale' => null, 'scope' => null]],
                'ean' => [['data' => 'ean', 'locale' => null, 'scope' => null]],
            ],
            ['master_accessories_belts'],
            ['related'],
            ['X_SELL' => ['products' => ['1111111171']]]
        );

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save($this->product);

        $this->productModel = $this->getFromTestContainer(
            'akeneo_integration_tests.catalog.factory.product_model'
        )->create(
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

        $this->getFromTestContainer('akeneo_integration_tests.fixture.saver.entity_with_value')->save(
            $this->productModel
        );
    }

    /**
     * To update product into a variant product in database we need to check that:
     *   - the parent product model is well set
     *   - the raw value are well updated
     *   - product type are well changed (data managed by doctrine)
     *   - variant product keeps the product id
     *   - the repository returns the right object type (variant product instead of product)
     */
    public function test query that turn product into variant product in database()
    {
        /** @var VariantProductInterface $inMemoryVariantProduct */
        $inMemoryVariantProduct = $this->get('pim_catalog.entity_with_family.create_variant_product_from_product')
            ->from($this->product, $this->productModel);

        $this->get('pim_catalog.doctrine.query.turn_product_into_variant_product')->into($inMemoryVariantProduct);

        /** @var VariantProductInterface $variantProduct */
        $variantProduct = $this->get('pim_catalog.repository.variant_product')->findOneByIdentifier('my-product');

        $this->assertInstanceOf(VariantProductInterface::class, $variantProduct);
        $this->assertSame($inMemoryVariantProduct->getId(), $variantProduct->getId());
        $this->assertSame($inMemoryVariantProduct->getParent()->getId(), $variantProduct->getParent()->getId());

        $assertValueCollection = new Assertion\ValuesCollection(
            ['sku', 'size', 'ean'],
            $variantProduct->getValuesForVariation()
        );
        $assertValueCollection->hasSameValues();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}