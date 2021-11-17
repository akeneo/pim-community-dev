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

final class DoesNotContainFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::DOES_NOT_CONTAIN;
    }

    /** @test */
    public function it_filters_on_does_not_contain_operator(): void
    {
        $this->createProductWithNutrition('empty_product', [], 'family_with_table');
        $this->createProductWithNutrition('product_with_a_text', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'allergen' => false,
                'additional_info' => 'this is a text',
                'nutrition_score' => 'B',
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 20.5,
                'allergen' => true,
                'nutrition_score' => 'C',
            ],
        ]);
        $this->createProductWithNutrition('product_with_another_text', [
            [
                'ingredient' => 'sugar',
                'quantity' => 50,
                'allergen' => false,
            ],
            [
                'ingredient' => 'egg',
                'quantity' => 22,
                'allergen' => false,
                'additional_info' => 'this is another text',
                'nutrition_score' => 'C',
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'value' => 'TEXT'],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'value' => 'text'],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_INFO', 'value' => 'another'],
            ['product_with_a_text']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'value' => 'unknown'],
            ['product_with_a_text', 'product_with_another_text']
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'Sugar', 'value' => 'text'],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'egg', 'value' => 'text'],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'egg', 'value' => 'another'],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'row' => 'egg', 'value' => 'pouet'],
            ['product_with_another_text']
        );

        $this->expectException(InvalidPropertyTypeException::class);
        $this->assertFilter(
            'nutrition',
            ['column' => 'additional_info', 'value' => []],
            []
        );
    }

    /** @test */
    public function it_filters_on_does_not_contain_operator_with_locale_and_scope(): void
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
                            'additional_info' => 'this is a text',
                            'nutrition_score' => 'C',
                        ],
                        [
                            'ingredient' => 'egg',
                            'quantity' => 20.5,
                            'allergen' => true,
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
                            'additional_info' => 'bar',
                        ],
                    ],
                ],
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // On column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'this is a'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'bar'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'additional_info', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 'bar'],
            []
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'Additional_info', 'row' => 'SUGAR', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'this is'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'Additional_info', 'row' => 'SUGAR', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'bar'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'Additional_info', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 'bar'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'Additional_info', 'row' => 'EGG', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 'bar'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'Additional_info', 'row' => 'EGG', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 'foo'],
            ['product']
        );
    }
}
