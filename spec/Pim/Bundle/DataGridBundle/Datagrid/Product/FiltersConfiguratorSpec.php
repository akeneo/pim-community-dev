<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $this->beConstructedWith($configuration, $registry, 'Pim/Catalog/Product');
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }

    function it_configures_datagrid_filters($configuration, $registry)
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

        $this->configure();
    }

    function it_cannot_handle_misconfigured_attribute_type($configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'                => 'sku',
                'label'               => 'Sku',
                'useableAsGridFilter' => 1,
                'attributeType'       => 'pim_catalog_identifier',
                'sortOrder'           => 2,
                'group'               => 'Foo',
                'groupOrder'          => 3
            ],
            'name' => [
                'code'                => 'name',
                'label'               => 'Name',
                'useableAsGridFilter' => 1,
                'attributeType'       => 'pim_catalog_text',
                'sortOrder'           => 4,
                'group'               => 'Bar',
                'groupOrder'          => 5
            ]
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn([]);

        $this->shouldThrow('\LogicException')->duringConfigure();
    }
}
