<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Query\Sql;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;

/**
 * @group ce
 */
class ListProductGridAvailableColumnsIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_fetch_available_columns_without_search(): void
    {
        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code'                   => "not_useable_att",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'useable_as_grid_filter' => false,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute not useable in datagrid"]
        ]);

        // Index decremented to check that the creation order has no impact on the result order
        for($i = 15; $i >= 1; $i--)
        {
            $attributes[] = $this->createAttribute([
                'code'                   => "att_$i",
                'type'                   => 'pim_catalog_text',
                'group'                  => 'other',
                'useable_as_grid_filter' => true,
                'sort_order'             => $i,
                'labels'                 => ['en_US' => "Attribute $i"]
            ]);
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $expectedColumns = $this->getSystemColumns();
        $expectedColumns ['sku'] = [
            'code' => 'sku',
            'label' => '[sku]'
        ];

        for ($i = 1; $i < 14; $i++) {
            $code = "att_$i";
            $expectedColumns[$code] = [
                'code' => $code,
                'label' => "Attribute $i",
            ];
        }

        $listAvailableColumnsQuery = $this->get('pim_datagrid.product_grid.query.list_available_columns');
        $availableColumns = $listAvailableColumnsQuery->fetch('en_US', 1, '', '', 2);

        $this->assertSame($expectedColumns, $availableColumns);

        $expectedColumnsPage2 = [
            'att_14' => [
                'code' => 'att_14',
                'label' => "Attribute 14",
            ],
            'att_15' => [
                'code' => 'att_15',
                'label' => "Attribute 15",
            ]
        ];

        $availableColumnsPage2 = $listAvailableColumnsQuery->fetch('en_US', 2, '', '', 2);
        $this->assertSame($expectedColumnsPage2, $availableColumnsPage2);
    }

    public function test_fetch_available_columns_with_search_on_label(): void
    {
        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code' => 1234,
            'type' => 'pim_catalog_text',
            'group' => 'other',
            'useable_as_grid_filter' => true,
            'sort_order' => 2,
            'labels' => ['en_US' => "Attribute that matches the search label"]
        ]);

        $attributes[] = $this->createAttribute([
            'code' => "att_ok_matches_without_label",
            'type' => 'pim_catalog_text',
            'group' => 'other',
            'useable_as_grid_filter' => true,
            'sort_order' => 1,
        ]);

        $attributes[] = $this->createAttribute([
            'code' => "att_ko",
            'type' => 'pim_catalog_text',
            'group' => 'other',
            'useable_as_grid_filter' => false,
            'sort_order' => 2,
            'labels' => ['en_US' => "Attribute that matches the search label but is not useable in grid"]
        ]);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $expectedColumns = [
            'label' => [
                'code' => 'label',
                'label' => 'Label'
            ],
            'att_ok_matches_without_label' => [
                'code' => 'att_ok_matches_without_label',
                'label' => '[att_ok_matches_without_label]',
            ],
            1234 => [
                'code' => '1234',
                'label' => 'Attribute that matches the search label',
            ]
        ];

        $availableColumns = $this->get('pim_datagrid.product_grid.query.list_available_columns')->fetch('en_US', 1, '', 'label', 2);

        $this->assertSame($expectedColumns, $availableColumns);
    }

    public function  test_fetch_available_columns_filtered_by_group(): void
    {
        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code'                   => "att_ko",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'useable_as_grid_filter' => true,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute of group A"]
        ]);
        $attributes[] = $this->createAttribute([
            'code'                   => "att_ok_1",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'other',
            'useable_as_grid_filter' => true,
            'sort_order'             => 2,
            'labels'                 => ['en_US' => "Expected attribute 1"]
        ]);
        $attributes[] = $this->createAttribute([
            'code'                   => "att_ok_2",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'other',
            'useable_as_grid_filter' => true,
            'sort_order'             => 3,
            'labels'                 => ['en_US' => "Expected attribute 2"]
        ]);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $expectedColumns = [
            'att_ok_1' => [
                'code' => 'att_ok_1',
                'label' => 'Expected attribute 1',
            ],
            'att_ok_2' => [
                'code' => 'att_ok_2',
                'label' => 'Expected attribute 2',
            ]
        ];

        $availableColumns = $this->get('pim_datagrid.product_grid.query.list_available_columns')->fetch('en_US', 1, 'other', '', 2);

        $this->assertSame($expectedColumns, $availableColumns);
    }

    /**
     * @param array $attributeData
     *
     * @return Attribute
     */
    private function createAttribute(array $attributeData): Attribute
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);

        return $attribute;
    }

    /**
     * @return array
     */
    private function getSystemColumns(): array
    {
        return [
            'identifier' =>
                [
                    'code' => 'identifier',
                    'label' => 'ID',
                ],
            'image' =>
                [
                    'code' => 'image',
                    'label' => 'Image',
                ],
            'label' =>
                [
                    'code' => 'label',
                    'label' => 'Label',
                ],
            'family' =>
                [
                    'code' => 'family',
                    'label' => 'Family',
                ],
            'enabled' =>
                [
                    'code' => 'enabled',
                    'label' => 'Status',
                ],
            'completeness' =>
                [
                    'code' => 'completeness',
                    'label' => 'Complete',
                ],
            'created' =>
                [
                    'code' => 'created',
                    'label' => 'Created At',
                ],
            'updated' =>
                [
                    'code' => 'updated',
                    'label' => 'Updated At',
                ],
            'complete_variant_products' =>
                [
                    'code' => 'complete_variant_products',
                    'label' => 'Variant products',
                ],
            'groups' =>
                [
                    'code' => 'groups',
                    'label' => 'Groups',
                ],
            'parent' =>
                [
                    'code' => 'parent',
                    'label' => 'Parent',
                ],
        ];
    }
}
