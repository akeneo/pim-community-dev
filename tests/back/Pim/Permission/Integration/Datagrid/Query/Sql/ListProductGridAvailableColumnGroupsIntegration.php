<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Datagrid\Query\Sql;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;

class ListProductGridAvailableColumnGroupsIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_fetch_available_column_groups(): void
    {
        $attributes[] = $this->createAttribute([
            'code'                   => "not_useable_att",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupB',
            'useable_as_grid_filter' => false,
        ]);
        $attributes[] = $this->createAttribute([
            'code'                   => "att_1",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupB',
            'useable_as_grid_filter' => true,
        ]);
        $attributes[] = $this->createAttribute([
            'code'                   => "att_2",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupB',
            'useable_as_grid_filter' => true,
        ]);
        $attributes[] = $this->createAttribute([
            'code'                   => "att_without_view_permission",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupC',
            'useable_as_grid_filter' => true,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute without view permission"]
        ]);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $availableColumnGroups = $this->get('pimee_security.product_grid.query.list_available_column_groups')
            ->fetch('en_US', $user->getId());

        $expectedColumnGroups = [
            [
                'code'  => 'system',
                'count' => 11,
                'label' => 'System',
            ],
            [
                'code'  => 'attributeGroupA',
                'count' => 1,
                'label' => 'Attribute group A'
            ],
            [
                'code'  => 'attributeGroupB',
                'count' => 2,
                'label' => 'Attribute group B'
            ]
        ];

        $this->assertSame($expectedColumnGroups, $availableColumnGroups);
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
}
