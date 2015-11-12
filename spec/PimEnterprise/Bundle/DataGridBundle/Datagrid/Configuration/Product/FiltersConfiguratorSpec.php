<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $this->beConstructedWith($registry, 'Pim/Catalog/ProductInterface');
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }

    function it_overrides_base_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator');
    }

    function it_adds_is_owner_filter($configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'                => 'sku',
                'label'               => 'Sku',
                'useableAsGridFilter' => 1,
                'attributeType'       => 'pim_catalog_identifier',
                'sortOrder'           => 1,
                'group'               => 'General',
                'groupOrder'          => 1
            ],
            'name' => [
                'code'                => 'name',
                'label'               => 'Name',
                'useableAsGridFilter' => 1,
                'attributeType'       => 'pim_catalog_text',
                'sortOrder'           => 2,
                'group'               => 'General',
                'groupOrder'          => 1
            ]
        ];
        $key = 'source';
        $configuration->offsetGet($key)->willReturn([
            ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes
        ]);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn(['filter' => ['text_config']]);

        $expectedConf1 = [
            0            => 'identifier_config',
            'data_name'  => 'sku',
            'label'      => 'Sku',
            'enabled'    => true,
            'order'      => 1,
            'group'      => 'General',
            'groupOrder' => 1
        ];
        $expectedConf2 = [
            0            => 'text_config',
            'data_name'  => 'name',
            'label'      => 'Name',
            'enabled'    => false,
            'order'      => 2,
            'group'      => 'General',
            'groupOrder' => 1
        ];
        $configuration->offsetSet(FilterConfiguration::FILTERS_KEY, [
            'columns' => [
                'sku' => $expectedConf1,
                'name' => $expectedConf2
            ]
        ])->willReturn($configuration);
        $configuration->offsetGet(FilterConfiguration::FILTERS_KEY)->willReturn([]);

        // and it adds the is owner filter
        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'permission');
        $expectedConf = [
            'type'      => 'product_permission',
            'ftype'     => 'choice',
            'data_name' => 'permissions',
            'label'     => 'pimee_workflow.product.permission.label',
            'options'   => [
                'field_options' => [
                    'multiple' => false,
                    'choices'  => [
                        3 => 'pimee_workflow.product.permission.own',
                        2 => 'pimee_workflow.product.permission.edit',
                        1 => 'pimee_workflow.product.permission.view',
                    ]
                ]
            ]
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->shouldBeCalled();

        $this->configure($configuration);
    }
}
