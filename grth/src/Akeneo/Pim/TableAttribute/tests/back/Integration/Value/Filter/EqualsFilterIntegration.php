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

final class EqualsFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::EQUALS;
    }

    /** @test */
    public function it_filters_on_equals_operator(): void
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
                'allergen' => false,
                'additional_info' => 'foo',
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 22,
                'allergen' => false,
                'additional_info' => 'bar',
                'nutrition_score' => 'C',
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 50],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 20.5],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'value' => true],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'value' => false],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'value' => 'foo'],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'QUantity', 'row' => 'Sugar', 'value' => 50],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'QUantity', 'row' => 'Sugar', 'value' => 20],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'QUantity', 'row' => 'egg', 'value' => 50],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'row' => 'egg', 'value' => true],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'row' => 'egg', 'value' => false],
            ['product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'row' => 'pepper', 'value' => false],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'row' => 'pepper', 'value' => true],
            []
        );

        $this->expectException(InvalidPropertyTypeException::class);
        $this->assertFilter(
            'nutrition',
            ['column' => 'allergen', 'value' => []],
            []
        );
    }

    /** @test */
    public function it_filters_on_equals_operator_with_locale_and_scope(): void
    {
        $this->createProductWithValues('empty_product', [], 'family_with_table');
        $this->createProductWithValues('product_with_empty_cell_filled_on_english_ecommerce', [
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
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // On column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 50],
            ['product_with_empty_cell_filled_on_english_ecommerce']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 30],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 30],
            ['product_with_empty_cell_filled_on_english_ecommerce']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 50],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'allergen', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => true],
            ['product_with_empty_cell_filled_on_english_ecommerce']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'allergen', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => false],
            ['product_with_empty_cell_filled_on_english_ecommerce']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'allergen', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => true],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'allergen', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => false],
            ['product_with_empty_cell_filled_on_english_ecommerce']
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'foo'],
            ['product_with_empty_cell_filled_on_english_ecommerce']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'row' => 'egg', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'foo'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 'foo'],
            []
        );
    }
}
