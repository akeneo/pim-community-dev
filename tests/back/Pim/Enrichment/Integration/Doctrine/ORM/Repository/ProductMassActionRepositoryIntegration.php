<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionRepositoryIntegration extends TestCase
{
    public function testCanMassDeleteNonVariantProduct()
    {
        $product = $this->createNonVariantProduct();
        $productId = $product->getId();

        $deletedProductsCount = $this->get('pim_catalog.repository.product_mass_action')->deleteFromIds([$productId]);
        $this->assertSame(1, $deletedProductsCount);
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneById($productId));
    }

    public function testCanMassDeleteVariantProduct()
    {
        $variantProduct = $this->createVariantProduct();
        $variantProductId = $variantProduct->getId();

        $deletedVariantProductsCount = $this->get('pim_catalog.repository.product_mass_action')->deleteFromIds(
            [$variantProductId]
        );
        $this->assertSame(1, $deletedVariantProductsCount);
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneById($variantProductId));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @return ProductInterface
     */
    private function createNonVariantProduct(): ProductInterface
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProduct('a_product', 'familyA', []);
    }

    /**
     * @return ProductInterface
     */
    private function createVariantProduct(): ProductInterface
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $productModel = $entityBuilder->createProductModel('a_product_model', 'familyVariantA2', null, []);

        return $entityBuilder->createVariantProduct(
            'a_variant_product',
            'familyA',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
            ]
        );
    }
}
