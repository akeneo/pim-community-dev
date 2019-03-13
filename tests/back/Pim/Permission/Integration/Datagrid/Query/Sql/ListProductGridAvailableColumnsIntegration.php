<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Datagrid\Query\Sql;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\PimDataGridBundle\Query\ListProductGridAvailableColumns;

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
        $expectedColumns = $this->getSystemColumns();
        $expectedColumns ['sku'] = [
            'code' => 'sku',
            'label' => '[sku]'
        ];

        $numberOfAttributesToCreate = max(0, ListProductGridAvailableColumns::COLUMNS_PER_PAGE - count($expectedColumns)) + 2;
        for ($i = 1; $i <= $numberOfAttributesToCreate; $i++) {
            $code = "att_$i";
            $expectedColumns[$code] = [
                'code' => $code,
                'label' => "Attribute $i",
            ];
        }

        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code'                   => "att_without_view_pemission",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupC',
            'useable_as_grid_filter' => true,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute without view permission"]
        ]);

        $attributes[] = $this->createAttribute([
            'code'                   => "not_useable_att",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'useable_as_grid_filter' => false,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute not useable in datagrid"]
        ]);

        // Index decremented to check that the creation order has no impact on the result order
        for($i = $numberOfAttributesToCreate; $i >= 1; $i--)
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

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');

        $expectedColumnsPage1 = array_slice($expectedColumns, 0, ListProductGridAvailableColumns::COLUMNS_PER_PAGE);
        $expectedColumnsPage2 = array_slice($expectedColumns, ListProductGridAvailableColumns::COLUMNS_PER_PAGE);

        $availableColumns = $this->get('pimee_security.product_grid.query.list_available_columns')
            ->fetch('en_US', 1, '', '', $user->getId());
        $this->assertSame($expectedColumnsPage1, $availableColumns, 'Page 1');

        $availableColumnsPage2 = $this->get('pimee_security.product_grid.query.list_available_columns')
            ->fetch('en_US', 2, '', '', $user->getId());

        $this->assertSame($expectedColumnsPage2, $availableColumnsPage2, 'Page 2');
    }

    public function test_fetch_available_columns_with_search_on_label(): void
    {
        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code' => "att_ok",
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

        $attributes[] = $this->createAttribute([
            'code'                   => "att_without_view_pemission",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupC',
            'useable_as_grid_filter' => true,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute that matches the search label but without view permission"]
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
            'att_ok' => [
                'code' => 'att_ok',
                'label' => 'Attribute that matches the search label',
            ]
        ];

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');

        $availableColumns = $this->get('pimee_security.product_grid.query.list_available_columns')
            ->fetch('en_US', 1, '', 'label', $user->getId());

        $this->assertSame($expectedColumns, $availableColumns);
    }

    public function  test_fetch_available_columns_filtered_by_group(): void
    {
        $attributes = [];
        $attributes[] = $this->createAttribute([
            'code'                   => "att_without_view_pemission",
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupC',
            'useable_as_grid_filter' => true,
            'sort_order'             => 1,
            'labels'                 => ['en_US' => "Attribute without view permission"]
        ]);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $availableColumns = $this->get('pimee_security.product_grid.query.list_available_columns')
            ->fetch('en_US', 1, 'attributeGroupC', '', $user->getId());

        $this->assertSame([], $availableColumns);
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
     * The list of the system columns cannot be determined statically because they are extensible.
     *
     * @return array
     */
    private function getSystemColumns(): array
    {
        $datagridConfiguration = $this->get('oro_datagrid.configuration.provider.chain')->getConfiguration('product-grid');

        $configurationColumns = $datagridConfiguration->offsetGetByPath(
                sprintf('[%s]', Configuration::COLUMNS_KEY), []
            ) + $datagridConfiguration->offsetGetByPath(
                sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY), []
            );

        $systemColumns = [];
        foreach ($configurationColumns as $code => $column) {
            $systemColumns[$code] = [
                'code'  => $code,
                'label' => $column['label']
            ];
        }

        return $systemColumns;
    }
}
