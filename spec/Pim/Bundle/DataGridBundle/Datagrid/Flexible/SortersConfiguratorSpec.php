<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;


class SortersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $attributes = ['sku' => $sku, 'name' => $name];
        $callback = function () {};
        $this->beConstructedWith($configuration, $registry, $attributes, $callback);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface');
    }

    function it_configures_datagrid_columns(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $name->getAttributeType()->willReturn('pim_catalog_text');

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['column' => ['identifier_config'], 'sorter' => []]);
        $registry->getConfiguration('pim_catalog_text')->willReturn(['column' => ['text_config'], 'sorter' => []]);

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $configuration->offsetGetByPath($columnConfPath)->willReturn(['family' => ['family_config'], 'sku' => [], 'name' => []]);

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'sku');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'name');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $this->configure();
    }

    function it_cannot_handle_misconfigured_attribute_type(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $name->getAttributeType()->willReturn('pim_catalog_text');

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['column' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn([]);

        $this->shouldThrow('\LogicException')->duringConfigure();
    }
}
