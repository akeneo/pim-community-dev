<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetNonRequiredProductModelAttributesMaskQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetNonRequiredProductModelTableAttributesMaskQueryIntegration extends CompletenessTableAttributeTestCase
{
    public function test_it_gives_the_non_required_table_attributes_masks_for_a_product_model_with_one_level_of_variation()
    {
        $this->givenADeactivatedAttributeGroup('erp');
        $this->givenAttributes([
            // Attributes for the family variant
            ['code' => 'a_required_variant_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_variation_axis', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
            // Attributes non required but deactivated from their attribute group
            ['code' => 'a_deactivated_variant_text', 'type' => AttributeTypes::TEXT, 'group' => 'erp'],
            // A table attribute because the handling is different than other attribute
            [
                'code' => 'a_localizable_scopable_table',
                'type' => AttributeTypes::TABLE,
                'localizable' => true,
                'scopable' => true,
                'table_configuration' => [
                    [
                        'code' => 'column_1',
                        'data_type' => 'select',
                        'labels' => [],
                        'options' => [
                            ['code' => 'option_1'],
                            ['code' => 'option_2'],
                            ['code' => 'option_3'],
                        ],
                    ],
                    [
                        'code' => 'column_2',
                        'data_type' => 'text',
                        'labels' => [],
                    ],
                ],
            ],
            [
                'code' => 'a_non_localizable_scopable_table',
                'type' => AttributeTypes::TABLE,
                'table_configuration' => [
                    [
                        'code' => 'column_1',
                        'data_type' => 'select',
                        'labels' => [],
                        'options' => [
                            ['code' => 'option_1'],
                            ['code' => 'option_2'],
                            ['code' => 'option_3'],
                        ],
                    ],
                    [
                        'code' => 'column_2',
                        'data_type' => 'text',
                        'labels' => [],
                    ],
                ],
            ]
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_variation_axis',
                    'a_required_variant_text',
                    'a_deactivated_variant_text',
                    'a_localizable_scopable_table',
                    'a_non_localizable_scopable_table'
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_variation_axis',
                        'a_required_variant_text',
                    ],
                ]
            ],
        ]);

        $this->givenAFamilyVariant([
            'code' => 'familyA_variant1',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_variation_axis'],
                    'attributes' => ['a_variation_axis', 'sku', 'a_required_variant_text', 'a_deactivated_variant_text'],
                ],
            ],
        ]);

        $productModelId = $this->givenAProductModel('a_product_model', 'familyA_variant1');
        $attributesMask = $this->get(GetNonRequiredProductModelAttributesMaskQuery::class)->execute($productModelId);

        $ecommerceEnUsMask = $attributesMask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');

        $this->assertEqualsCanonicalizing([
            \sprintf(
                'a_localizable_scopable_table-%s-ecommerce-en_US',
                $this->getColumnId('a_localizable_scopable_table', 'column_2')
            ),
            \sprintf('a_non_localizable_scopable_table-%s-<all_channels>-<all_locales>',
                $this->getColumnId('a_non_localizable_scopable_table', 'column_2')),
        ], $ecommerceEnUsMask->mask());

        $unknownProductId = new ProductModelId(42);
        $this->assertNull($this->get(GetNonRequiredProductModelAttributesMaskQuery::class)->execute($unknownProductId));
    }

    private function getColumnId(string $attributeCode, string $columnCode): string
    {
        $query = <<<SQL
SELECT c.id
FROM pim_catalog_table_column c
    JOIN pim_catalog_attribute a ON a.id = c.attribute_id
WHERE a.code = :attribute_code AND c.code = :column_code
SQL;
        return $this->get('database_connection')->executeQuery($query, [
            'attribute_code' => $attributeCode,
            'column_code' => $columnCode,
        ])->fetchOne();
    }
}
