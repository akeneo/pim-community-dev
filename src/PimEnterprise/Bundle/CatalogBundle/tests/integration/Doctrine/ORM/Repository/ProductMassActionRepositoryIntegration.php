<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\tests\integration\Doctrine\ORM\Repository;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
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
        $entityBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProduct('a_product', 'familyA', []);
    }

    /**
     * @return ProductInterface
     */
    private function createVariantProduct(): ProductInterface
    {
        $entityBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.build_entity');

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
