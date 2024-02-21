<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\integration\Query\Sql;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesUseableInProductGrid;

class ListAttributesUsableInProductGridIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testFetchAttributesUsableInProductGridWithoutSearch(): void
    {
        $attributes = [];
        // Index decremented to check that the creation order has no impact on the result order
        for($i = ListAttributesUseableInProductGrid::ATTRIBUTES_PER_PAGE - 1; $i >= 1; $i--)
        {
            $attributes[] = $this->createAttribute([
                'code' => "att_groupA_$i",
                'type' => 'pim_catalog_text',
                'group' => 'attributeGroupA',
                'useable_as_grid_filter' => true,
                'sort_order' => $i,
                'labels' => ['en_US' => "Attribute group A $i"]
            ]);
        }

        for($i = 2; $i >= 1; $i--)
        {
            $attributes[] = $this->createAttribute([
                'code' => "att_other_$i",
                'type' => 'pim_catalog_text',
                'group' => 'other',
                'useable_as_grid_filter' => true,
                'sort_order' => $i,
                'labels' => ['en_US' => "Attribute other $i"]
            ]);
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $firstPageAttributes = $this->get('pim_datagrid.product_grid.query.list_attributes')->fetch('en_US', 1, '', 2);

        $expectedAttributes = [[
            'code'         => 'sku',
            'type'         => 'pim_catalog_identifier',
            'order'        => 0,
            'groupOrder'   => 1,
            'metricFamily' => null,
            'label'        => '[sku]',
            'group'        => 'Attribute group A',
        ]];

        for($i = 1; $i <= ListAttributesUseableInProductGrid::ATTRIBUTES_PER_PAGE - 1; $i++)
        {
            $expectedAttributes[] = [
                'code'         => "att_groupA_$i",
                'type'         => "pim_catalog_text",
                'order'        => $i,
                'metricFamily' => null,
                'groupOrder'   => 1,
                'label'        => "Attribute group A $i",
                'group'        => "Attribute group A",
            ];
        }

        $this->assertSameAttributes($expectedAttributes , $firstPageAttributes);

        $secondPageAttributes = $this->get('pim_datagrid.product_grid.query.list_attributes')->fetch('en_US', 2, '', 3);

        $this->assertSameAttributes([
            [
                'code'         => 'att_other_1',
                'type'         => 'pim_catalog_text',
                'order'        => 1,
                'metricFamily' => null,
                'groupOrder'   => 100,
                'label'        => 'Attribute other 1',
                'group'        => 'Other',
            ],
            [
                'code'         => 'att_other_2',
                'type'         => 'pim_catalog_text',
                'order'        => 2,
                'metricFamily' => null,
                'groupOrder'   => 100,
                'label'        => 'Attribute other 2',
                'group'        => 'Other',
            ],
        ], $secondPageAttributes);
    }

    public function testFetchAttributesUsableInProductGridWithSearch()
    {
        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code' => "att_ok",
            'type' => 'pim_catalog_text',
            'group' => 'other',
            'useable_as_grid_filter' => true,
            'sort_order' => 2,
            'labels' => ['en_US' => "Attribute that matches the search"]
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
            'labels' => ['en_US' => "Attribute that matches the search but is not useable in grid"]
        ]);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $attributes = $this->get('pim_datagrid.product_grid.query.list_attributes')->fetch('en_US', 1, 'match', 2) ;

        $this->assertSameAttributes([
            [
                'code'         => 'att_ok_matches_without_label',
                'type'         => 'pim_catalog_text',
                'order'        => 1,
                'metricFamily' => null,
                'groupOrder'   => 100,
                'label'        => '[att_ok_matches_without_label]',
                'group'        => 'Other',
            ],
            [
                'code'         => 'att_ok',
                'type'         => 'pim_catalog_text',
                'order'        => 2,
                'metricFamily' => null,
                'groupOrder'   => 100,
                'label'        => 'Attribute that matches the search',
                'group'        => 'Other',
            ],
        ], $attributes);
    }


    /**
     * @param array $attributeData
     * @return Attribute
     */
    private function createAttribute(array $attributeData): Attribute
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);

        return $attribute;
    }

    /**
     * Assert that two array of attributes are the same.
     * The order of the attributes is important, but not the order of the data inside each attribute.
     *
     * @param array $expectedAttributes
     * @param array $currentAttributes
     */
    private function assertSameAttributes(array $expectedAttributes, array $currentAttributes): void
    {
        $expectedAttributes = array_map(function($attributeData) {
            ksort($attributeData);
            return $attributeData;
        }, $expectedAttributes);

        $currentAttributes = array_map(function($attributeData) {
            ksort($attributeData);
            return $attributeData;
        }, $currentAttributes);

        $this->assertSame($expectedAttributes, $currentAttributes);
    }
}
