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

final class GreaterThanFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::GREATER_THAN;
    }

    /** @test */
    public function it_filters_on_greater_than_operator(): void
    {
        $this->createProductWithNutrition('empty_product', [], 'family_with_table');
        $this->createProductWithNutrition('cheese_cake', [
            ['ingredient' => 'sugar', 'quantity' => 100],
            ['ingredient' => 'cheese', 'quantity' => 200],
        ]);
        $this->createProductWithNutrition('chocolate_cake', [
            ['ingredient' => 'sugar', 'quantity' => 300],
            ['ingredient' => 'chocolate', 'quantity' => 200],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 50],
            ['cheese_cake', 'chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 200],
            ['chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 299],
            ['chocolate_cake']
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'value' => 99],
            ['cheese_cake', 'chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'value' => 100],
            ['chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'value' => 199],
            ['cheese_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'value' => 200],
            []
        );

        $this->expectException(InvalidPropertyTypeException::class);
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 'abc'],
            []
        );
    }

    /** @test */
    public function it_filters_on_greater_than_operator_with_locale_and_scope(): void
    {
        $this->createProductWithValues('empty_product', [], 'family_with_table');
        $this->createProductWithValues('cheese_cake', [
            'localizable_scopable_nutrition' => [
                [
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                    'data' => [
                        ['ingredient' => 'sugar', 'quantity' => 100],
                        ['ingredient' => 'cheese', 'quantity' => 200],
                    ],
                ],
                [
                    'locale' => 'fr_FR',
                    'scope' => 'mobile',
                    'data' => [
                        ['ingredient' => 'sugar', 'quantity' => 100],
                        ['ingredient' => 'cheese', 'quantity' => 300],
                    ],
                ],
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // On column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 99],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 199],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 199],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 200],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 200],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 300],
            []
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 299],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 299],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 199],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 200],
            []
        );
    }

    /** @test */
    public function it_filters_greater_than_values_for_a_measurement_column(): void
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
                            'energy_per_100g' => ['unit' => 'CALORIE', 'amount' => 5000],
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
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'CALORIE', 'amount' => 2000]],
            ['product_with_every_cell_filled', 'product_with_every_an_empty_measurement_cell']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => '2.77']],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'JOULE', 'amount' => 50]],
            ['product_with_every_cell_filled', 'product_with_every_an_empty_measurement_cell']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => 3]],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => 5]],
            []
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => '4.9'], 'row' => 'egg'],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition_with_measurement',
            ['column' => 'energy_per_100g', 'value' => ['unit' => 'KILOCALORIE', 'amount' => 5], 'row' => 'sugar'],
            []
        );
    }
}
