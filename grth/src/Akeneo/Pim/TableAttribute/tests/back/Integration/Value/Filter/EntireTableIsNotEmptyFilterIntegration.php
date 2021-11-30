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

final class EntireTableIsNotEmptyFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::IS_NOT_EMPTY;
    }

    /** @test */
    public function it_filters_on_is_not_empty_operator_on_entire_table(): void
    {
        $this->createProductWithNutrition('empty_product', [], 'family_with_table');
        $this->createProductWithNutrition('product', [
            [
                'ingredient' => 'sugar',
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition',
            [],
            ['product']
        );
    }

    /** @test */
    public function it_filters_on_is_not_empty_operator_on_entire_table_with_locale_and_scope(): void
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
                        ],
                    ],
                ],
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['locale' => 'en_US', 'scope' => 'mobile'],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            []
        );
    }
}
