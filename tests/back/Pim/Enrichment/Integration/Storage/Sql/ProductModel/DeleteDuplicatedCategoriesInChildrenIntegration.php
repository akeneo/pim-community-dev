<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteDuplicatedCategoriesInChildrenIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_deletes_duplicated_categories_of_product_variants()
    {
        // Given a product model with a single variation level and a product variant with duplicated categories
        $productModelOneLevel = $this->createProductModel([
            'code' => 'product_model_one_level',
            'family_variant' => 'familyVariantA2',
        ]);
        $this->createProduct('product_variant_one_level_with_duplicated_categories', 'familyA', [
            'parent' => 'product_model_one_level',
            'values' => [
                'a_simple_select' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'optionA'
                    ]
                ],
                'a_yes_no' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => true
                    ]
                ]
            ],
            'categories' => [
                'categoryA1',
                'categoryC',
            ]
        ]);
        $this->updateProductModel($productModelOneLevel, [
            'categories' => [
                'categoryA',
                'categoryA1',
            ]
        ]);

    }

//    public function test_it_deletes_duplicated_categories_of_sub_product_models()
//    {
//
//    }


    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function updateProductModel(ProductModelInterface $productModel, array $data): void
    {
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function createProduct(string $identifier, string $familyCode, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
