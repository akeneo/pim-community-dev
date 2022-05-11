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

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Pim\TableAttribute\Helper\FeatureHelper;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

final class NotInListFilterIntegration extends AbstractFilterIntegration
{
    protected function getTestedOperator(): string
    {
        return Operators::NOT_IN_LIST;
    }

    /** @test */
    public function it_filters_on_not_in_list_operator(): void
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
            [
                'ingredient' => 'flour',
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
            ['column' => 'nutrition_score', 'value' => ['b']],
            ['product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'value' => ['a', 'Z']],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'value' => ['a', 'Z', 'b']],
            []
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'value' => ['b']],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'egg', 'value' => ['b']],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'value' => ['a']],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'nutrition_score', 'row' => 'egg', 'value' => ['c']],
            []
        );

        // On first column
        $this->assertFilter(
            'nutrition',
            ['column' => 'INgredient', 'value' => ['pepper', 'flour']],
            ['product_with_empty_sugar_additional_info']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'INgredient', 'row' => 'flour', 'value' => ['pepper']],
            ['product_with_every_cell_filled']
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'INgredient', 'row' => 'flour', 'value' => ['pepper', 'flour']],
            []
        );
        $this->assertFilter(
            'nutrition',
            ['column' => 'INgredient', 'row' => 'sugar', 'value' => ['pepper', 'flour']],
            ['product_with_every_cell_filled', 'product_with_empty_sugar_additional_info']
        );

        $this->expectException(InvalidPropertyTypeException::class);
        $this->assertFilter(
            'nutrition',
            ['column' => 'ingredient', 'value' => ['abc', [], 'de']],
            []
        );
    }

    /** @test */
    public function it_filters_on_in_list_operator_with_locale_and_scope(): void
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
                            'additional_info' => 'D',
                            'nutrition_score' => 'C',
                        ],
                        [
                            'ingredient' => 'egg',
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
                            'ingredient' => 'flour',
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
            ['column' => 'nutrition_score', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => ['c']],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => ['D']],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => ['B']],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => ['A', 'c']],
            []
        );

        // On column + row
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => ['c']],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => ['D']],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'egg', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => ['a']],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'egg', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => ['a']],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'nutrition_score', 'row' => 'sugar', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => ['a']],
            []
        );

        // On first column
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'locale' => 'en_US', 'scope' => 'ecommerce', 'value' => ['flour']],
            ['product']
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'locale' => 'en_US', 'scope' => 'mobile', 'value' => ['flour']],
            []
        );
        $this->assertFilter(
            'localizable_scopable_nutrition',
            ['column' => 'ingredient', 'locale' => 'fr_FR', 'scope' => 'mobile', 'value' => ['flour']],
            []
        );
    }

    /** @test */
    public function it_filters_on_not_in_list_operator_on_reference_entity_column(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel.find_channels')
            ->setChannels([
                new Channel('ecommerce', ['en_US'], LabelCollection::fromArray(['en_US' => 'Ecommerce', 'de_DE' => 'Ecommerce', 'fr_FR' => 'Ecommerce']), ['USD'])
            ]);

        $this->createNutritionAttributeWithReferenceEntityColumn();
        $this->createProductWithValues('empty_product', [], 'family_with_table');
        $this->createProductWithValues('product_with_reference_entity', [
            'nutrition_with_ref_entity' => [[
                'locale' => null,
                'scope' => null,
                'data' => [
                    [
                        'ingredient' => 'sugar',
                        'brand_column' => 'Akeneo',
                    ],
                    [
                        'ingredient' => 'egg',
                        'brand_column' => 'Other',
                    ],
                    [
                        'ingredient' => 'flour',
                    ],
                ],
            ]],
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Filter on column
        $this->assertFilter(
            'nutrition_with_ref_entity',
            ['column' => 'brand_column', 'value' => ['Akeneo']],
            []
        );
        $this->assertFilter(
            'nutrition_with_ref_entity',
            ['column' => 'brand_column', 'value' => ['Other2']],
            ['product_with_reference_entity']
        );

        // Filter on column + row
        $this->assertFilter(
            'nutrition_with_ref_entity',
            ['column' => 'brand_column', 'row' => 'sugar', 'value' => ['Akeneo']],
            []
        );
        $this->assertFilter(
            'nutrition_with_ref_entity',
            ['column' => 'brand_column', 'row' => 'egg', 'value' => ['Akeneo']],
            ['product_with_reference_entity']
        );
    }
}
