<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Query;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProduct;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * Test the query function: Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\ConvertProductToVariantProduct
 */
final class ConvertProductToVariantProductIntegration extends TestCase
{
    /** @var ProductInterface */
    private $product;

    /** @var ProductModelInterface */
    private $productModel;

    /**
     * {@inheritdoc}
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
     *   - the product type is well changed (data managed by doctrine)
     *   - the variant product keeps its versions
     */
    public function test_query_that_converts_product_to_variant_product_in_database()
    {
        /** @var VariantProductInterface $inMemoryVariantProduct */
        $inMemoryVariantProduct = $this->getFromTestContainer('pim_catalog.entity_with_family.create_variant_product_from_product')
            ->from($this->product, $this->productModel);

        $this->getFromTestContainer('pim_catalog.doctrine.query.convert_product_to_variant_product')
            ->execute($inMemoryVariantProduct);

        $sql = <<<SQL
SELECT COUNT(id) 
FROM pim_versioning_version 
WHERE resource_id = :resource_id
AND resource_name = :resource_name
SQL;
        $numberOfVersion = (int) $this->getFromTestContainer('doctrine.dbal.default_connection')->fetchColumn($sql, [
            'resource_id' => $this->product->getId(),
            'resource_name' => VariantProduct::class
        ]);

        $this->assertSame(
            1,
            $numberOfVersion,
            'The variant product does not keep the product versions'
        );

        $sql = <<<SQL
SELECT product_type 
FROM pim_catalog_product 
WHERE id = :resource_id
SQL;
        $productType = $this->getFromTestContainer('doctrine.dbal.default_connection')->fetchColumn($sql, [
            'resource_id' => $this->product->getId(),
            'resource_name' => str_replace('\\', '\\', VariantProduct::class)
        ]);

        $this->assertSame(
            'variant_product',
            $productType,
            sprintf('The product does not have the right type, given %s, expected variant_product', $productType)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
