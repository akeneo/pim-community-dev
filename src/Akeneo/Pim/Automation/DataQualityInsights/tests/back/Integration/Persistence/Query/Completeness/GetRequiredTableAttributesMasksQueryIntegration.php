<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\GetRequiredAttributesMasksQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRequiredTableAttributesMasksQueryIntegration extends CompletenessTableAttributeTestCase
{
    public function test_it_gives_the_required_table_attributes_masks_for_a_list_of_families()
    {
        $this->givenADeactivatedAttributeGroup('erp');
        $this->givenAttributes([
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
                'code' => '1234',
                'attribute_codes' => [
                    'sku',
                    'a_localizable_scopable_table',
                    'a_non_localizable_scopable_table'
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_localizable_scopable_table',
                        'a_non_localizable_scopable_table'
                    ],
                ]
            ]
        ]);

        $result = $this->get(GetRequiredAttributesMasksQuery::class)->fromFamilyCodes(['1234']);
        $family1234Mask = $result['1234'];
        $this->assertCount(1, $family1234Mask->masks());

        $ecommerceEnUsMask = $family1234Mask->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US');

        $this->assertEqualsCanonicalizing([
            'sku-<all_channels>-<all_locales>',
            \sprintf(
                'a_localizable_scopable_table-%s-ecommerce-en_US',
                $this->getColumnId('a_localizable_scopable_table', 'column_2')
            ),
            \sprintf('a_non_localizable_scopable_table-%s-<all_channels>-<all_locales>',
                $this->getColumnId('a_non_localizable_scopable_table', 'column_2')),
        ], $ecommerceEnUsMask->mask());
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
