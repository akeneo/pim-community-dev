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

namespace Akeneo\Pim\TableAttribute\tests\back\Integration\Value\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

final class GreaterOrEqualThanFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::GREATER_OR_EQUAL_THAN;
    }

    /** @test */
    public function it_filters_on_greater_or_equal_than_operator(): void
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
            ['column' => 'quantity', 'value' => 200],
            ['cheese_cake', 'chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 201],
            ['chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 300],
            ['chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'value' => 301],
            []
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'value' => 100],
            ['cheese_cake', 'chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'value' => 101],
            ['chocolate_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'value' => 200],
            ['cheese_cake']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'value' => 201],
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
    public function it_filters_on_greater_or_equal_than_operator_with_locale_and_scope(): void
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
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 100],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 200],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 200],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 201],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 201],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 301],
            []
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => 300],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 300],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'cheese', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 200],
            ['cheese_cake']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'quantity', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => 201],
            []
        );
    }
}
