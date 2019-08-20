<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(ConfigurationRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement(ConfiguratorInterface::class);
    }

    function it_configures_datagrid_filters(DatagridConfiguration $configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'                => 'sku',
                'label'               => 'Sku',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_identifier',
                'sortOrder'           => 1,
                'group'               => 'General',
                'groupOrder'          => 1
            ],
            123456 => [
                'code'                => '123456',
                'label'               => 'Name',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 2,
                'group'               => 'General',
                'groupOrder'          => 1
            ]
        ];

        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->offsetGet(ContextConfigurator::SOURCE_KEY)->willReturn($attributesConf);
        $configuration->offsetGet(FilterConfiguration::FILTERS_KEY)->willReturn([]);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn(['filter' => ['text_config']]);

        $expectedConf = [
            'sku' => [
                0            => 'identifier_config',
                'data_name'  => 'sku',
                'label'      => 'Sku',
                'enabled'    => true,
                'order'      => 1,
                'group'      => 'General',
                'groupOrder' => 1
            ],
            '123456' => [
                0            => 'text_config',
                'data_name'  => '123456',
                'label'      => 'Name',
                'enabled'    => false,
                'order'      => 2,
                'group'      => 'General',
                'groupOrder' => 1
            ]
        ];

        $configuration->offsetSet(FilterConfiguration::FILTERS_KEY, [
            'columns' => $expectedConf
        ])->shouldBeCalled();

        $this->configure($configuration);
    }

    function it_cannot_handle_misconfigured_attribute_type(DatagridConfiguration $configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'                => 'sku',
                'label'               => 'Sku',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_identifier',
                'sortOrder'           => 2,
                'group'               => 'Foo',
                'groupOrder'          => 3
            ],
            'name' => [
                'code'                => 'name',
                'label'               => 'Name',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 4,
                'group'               => 'Bar',
                'groupOrder'          => 5
            ]
        ];

        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->offsetGet(ContextConfigurator::SOURCE_KEY)->willReturn($attributesConf);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn([]);

        $this->shouldThrow('\LogicException')->duringConfigure($configuration);
    }
}
