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

final class NotEmptyFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::IS_NOT_EMPTY;
    }

    /** @test */
    public function it_filters_on_not_empty_operator(): void
    {
        $this->createProductWithNutrition('empty_product', [], 'family_with_table');
        $this->createProductWithNutrition('product_with_additional_info_on_sugar', [
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
            ],
        ]);
        $this->createProductWithNutrition('product_with_no_additional_info', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'allergen' => false,
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 20.5,
                'allergen' => true,
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info'],
            ['product_with_additional_info_on_sugar']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'ingredient'],
            ['product_with_additional_info_on_sugar', 'product_with_no_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score'],
            []
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'SUgar'],
            ['product_with_additional_info_on_sugar']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'egg'],
            []
        );
    }

    /** @test */
    public function it_filters_on_not_empty_operator_with_locale_and_scope(): void
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

        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'egg', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'EGG', 'locale' => 'en_US', 'scope' => 'mobile'],
            ['product']
        );
    }
}
