<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

final class NotEqualFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::NOT_EQUAL;
    }

    /** @test */
    public function it_filters_on_not_equal_operator(): void
    {
        $this->createProductWithNutrition('empty_product', [], 'family_with_table');
        $this->createProductWithNutrition('product_with_every_cell_filled', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'allergen' => false,
                'additional_info' => 'foo',
                'nutrition_score' => 'B',
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 20.5,
                'allergen' => true,
                'additional_info' => 'bar',
                'nutrition_score' => 'C',
            ],
        ]);
        $this->createProductWithNutrition('product_with_empty_sugar_additional_info', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'additional_info' => 'bar',
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 22,
                'allergen' => false,
                'additional_info' => 'foo',
                'nutrition_score' => 'C',
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 50],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 20.5],
            ['product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 22],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 42],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'Sugar', 'value' => 'foo'],
            ['product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'egg', 'value' => 'foo'],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'sugar', 'value' => 'bar'],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'egg', 'value' => 'foobar'],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'row' => 'egg', 'value' => true],
            ['product_with_empty_sugar_additional_info']
        );
        // No value on product_with_empty_sugar_additional_info on allergen column and sugar row
        // -> the product should not appear
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'row' => 'sugar', 'value' => true],
            ['product_with_every_cell_filled']
        );

        $this->expectException(InvalidPropertyTypeException::class);
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'value' => []],
            []
        );
    }

    /** @test */
    public function it_filters_on_not_equal_operator_with_locale_and_scope(): void
    {
        $this->createProductWithValues('empty_product', [], 'family_with_table');
        $this->createProductWithValues('product', [
            'localizable_scopable_nutrition' => [
                [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                            'quantity' => 50,
                            'allergen' => false,
                            'additional_info' => 'foo',
                            'nutrition_score' => 'C',
                        ],
                        [
                            'ingredient' => 'egg',
                            'quantity' => 20.5,
                            'allergen' => true,
                            'additional_info' => 'bar',
                            'nutrition_score' => 'C',
                        ],
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                            'quantity' => 30,
                        ],
                        [
                            'ingredient' => 'egg',
                            'allergen' => false,
                        ],
                    ],
                ],
                [
                    'locale' => 'fr_FR',
                    'scope' => 'mobile',
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                        ],
                        [
                            'ingredient' => 'egg',
                            'allergen' => false,
                        ],
                    ],
                ],
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // On column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 50],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 30],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 30],
            []
        );
        // No quantity column -> the product should not appear
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 30],
            []
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'bar'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'foo'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 'foo'],
            []
        );
    }

    /** @test */
    public function it_filters_not_equal_values_for_a_measurement_column(): void
    {
        $this->createNutritionAttributeWithMeasurementColumn();
        $this->createProductWithValues('empty_product', [], 'family_with_table');
        $this->createProductWithValues('product_with_every_cell_filled', [
            'nutrition_with_measurement' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                            'energy_per_100g' => ['unit' => 'KILOCALORIE', 'amount' => '2.77'],
                        ],
                        [
                            'ingredient' => 'egg',
                            'energy_per_100g' => ['unit' => 'CALORIE', 'amount' => '1500'],
                        ],
                    ],
                ],
            ],
        ]);
        $this->createProductWithValues('product_with_every_an_empty_measurement_cell', [
            'nutrition_with_measurement' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                        ],
                        [
                            'ingredient' => 'egg',
                            'energy_per_100g' => ['unit' => 'CALORIE', 'amount' => '2770'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'CALORIE', 'amount' => 2770]],
            []
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => '1.5']],
            ['product_with_every_an_empty_measurement_cell']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => 2]],
            ['product_with_every_cell_filled', 'product_with_every_an_empty_measurement_cell']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => '2.77'], 'row' => 'egg'],
            ['product_with_every_cell_filled']
        );
    }
}
