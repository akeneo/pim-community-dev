<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;

class ColumnsConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $sku->isUseableAsGridColumn()->willReturn(true);
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->getLabel()->willReturn('Sku');
        $name->isUseableAsGridColumn()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->getLabel()->willReturn('Name');

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(array('column' => array('identifier_config')));
        $registry->getConfiguration('pim_catalog_text')->willReturn(array('column' => array('text_config')));

        $attributes = array('sku' => $sku, 'name' => $name);
        $this->beConstructedWith($configuration, $registry, $attributes);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface');
    }

    function it_configures_datagrid_columns( $configuration)
    {
        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $columns = [
            'sku' => [
                'identifier_config',
                'label' => "Sku"
            ],
            'family' => [
                'family_config',
            ],
            'name' => [
                'text_config',
                'label' => "Name"
            ]
        ];

        $configuration->offsetGetByPath($columnConfPath)->willReturn(array('family' => array('family_config')));

        $configuration->offsetSetByPath($columnConfPath, $columns)->shouldBeCalled();
        $this->configure();
    }
}
