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

final class EmptyFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::IS_EMPTY;
    }

    /** @test */
    public function it_returns_empty_result_when_no_product_have_the_tabe_attribute(): void
    {
        // Reset index to remove dynamic mappings of previous tests
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->resetIndex();

        // Check there is no error (cf. CPM-404)
        $this->assertFilter('nutrition', ['column' => 'nutrition_score'], []);
    }

    /** @test */
    public function it_filters_on_empty_operator(): void
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
        $this->createProductWithNutrition('product_with_empty_sugar_nutrition_score', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'allergen' => false,
                'additional_info' => 'foo',
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 20.5,
                'allergen' => true,
                'additional_info' => 'bar',
                'nutrition_score' => 'C',
            ],
        ]);
        $this->createProductWithNutrition('product_with_no_additional_info', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'allergen' => false,
                'nutrition_score' => 'C',
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 20.5,
                'allergen' => true,
                'nutrition_score' => 'C',
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score'],
            ['product_with_empty_sugar_nutrition_score']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score'],
            ['product_with_empty_sugar_nutrition_score']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info'],
            ['product_with_no_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'sugar'],
            ['product_with_no_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'flour'],
            []
        );
        $this->assertFilter('nutrition', ['column' => 'nutrition_score', 'row' => 'egg'], []);

        // On first column
        $this->assertFilter(
            'nutrition',
            ['column' => 'ingredient', 'row' => 'pepper'],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_nutrition_score', 'product_with_no_additional_info']
        );
        $this->assertFilter('nutrition', ['column' => 'ingredient', 'row' => 'sugar'], []);
    }

    /** @test */
    public function it_filters_on_empty_operator_with_locale_and_scope(): void
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
                        ],
                        [
                            'ingredient' => 'egg',
                            'quantity' => 20.5,
                        ],
                        [
                            'ingredient' => 'flour',
                            'quantity' => 20.5,
                        ],
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                        ],
                        [
                            'ingredient' => 'egg',
                            'nutrition_score' => 'C',
                        ],
                    ],
                ],
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // On column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'locale' => 'fr_FR', 'scope' => 'mobile'],
            []
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'EGG', 'locale' => 'en_US', 'scope' => 'mobile'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'SUGAR', 'locale' => 'en_US', 'scope' => 'mobile'],
            ['product']
        );

        // On first column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'row' => 'flour', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'row' => 'pepper', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'row' => 'flour', 'locale' => 'en_US', 'scope' => 'mobile'],
            ['product']
        );
    }
}
