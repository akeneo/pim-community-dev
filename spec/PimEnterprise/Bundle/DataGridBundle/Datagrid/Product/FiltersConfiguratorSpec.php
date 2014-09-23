<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $this->beConstructedWith($registry, 'Pim/Catalog/ProductInterface');
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }

    function it_overrides_base_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator');
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
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn(['filter' => ['text_config']]);

        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'sku');
        $expectedConf = [
            0            => 'identifier_config',
            'data_name'  => 'sku',
            'label'      => 'Sku',
            'enabled'    => true,
            'order'      => 1,
            'group'      => 'General',
            'groupOrder' => 1
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->willReturn($configuration);
        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'name');
        $expectedConf = [
            0            => 'text_config',
            'data_name'  => 'name',
            'label'      => 'Name',
            'enabled'    => false,
            'order'      => 2,
            'group'      => 'General',
            'groupOrder' => 1
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->willReturn($configuration);

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
