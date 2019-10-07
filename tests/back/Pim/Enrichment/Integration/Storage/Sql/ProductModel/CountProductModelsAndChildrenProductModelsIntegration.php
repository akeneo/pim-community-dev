<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CountProductModelsAndChildrenProductModelsInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * Product models available for the tests:
 *
 * - a_shoes

 * - a_shirt
 *      - a_small_shirt
 *      - a_medium_shirt
 */
class CountProductModelsAndChildrenProductModelsIntegration extends TestCase
{
    public function test_it_counts_the_number_of_product_models_and_children_product_models_for_product_models(): void
    {
        // No product model.
        $result = $this->getQuery()->forProductModelCodes([]);
        self::assertEquals(0, $result);

        // Product model with 1 level of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_shoes']);
        self::assertEquals(1, $result);

        // Product model with 2 levels of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_shirt']);
        self::assertEquals(3, $result);

        // Multiple product models with multiple levels of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_shoes', 'a_shirt']);
        self::assertEquals(4, $result);

        // Level 1 product model of a product model with 2 levels of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_small_shirt']);
        self::assertEquals(1, $result);

        // Multiple levels 1 product models of the same product model with 2 levels of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_small_shirt', 'a_medium_shirt']);
        self::assertEquals(2, $result);

        // Duplicate of product models with 2 level of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_shirt', 'a_shirt']);
        self::assertEquals(3, $result);

        // Level 2 product model and level 1 product model of the same product model with 2 levels of variant.
        $result = $this->getQuery()->forProductModelCodes(['a_small_shirt', 'a_shirt']);
        self::assertEquals(3, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyVariant(
            [
                'code' => 'shoes_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['a_simple_select'],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $this->createProductModel(
            ['code' => 'a_shoes', 'family_variant' => 'shoes_color',]
        );

        $this->createFamilyVariant(
            [
                'code' => 'clothing_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_text'], 'level' => 2],
                ],
            ]
        );

        $this->createProductModel(
            ['code' => 'a_shirt', 'family_variant' => 'clothing_size_color',]
        );
        $this->createProductModel(
            [
                'code' => 'a_small_shirt',
                'family_variant' => 'clothing_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA',],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'a_medium_shirt',
                'family_variant' => 'clothing_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB',],
                    ],
                ],
            ]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): CountProductModelsAndChildrenProductModelsInterface
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.count_product_models_and_children_product_models');
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);

        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }
}
