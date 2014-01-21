<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $attributes = array('sku' => $sku, 'name' => $name);
        $this->beConstructedWith($configuration, $registry, $attributes, 'Pim/Catalog/Product');
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface');
    }

    function it_configures_datagrid_filters(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $sku->isUseableAsGridFilter()->willReturn(true);
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->getLabel()->willReturn('Sku');
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->getLabel()->willReturn('Name');

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(array('filter' => array('identifier_config')));
        $registry->getConfiguration('pim_catalog_text')->willReturn(array('filter' => array('text_config')));

        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'sku');
        $expectedConf = [
            0 => "identifier_config",
            "flexible_entity_name" => "Pim/Catalog/Product",
            "data_name" => "sku",
            "label" => "Sku",
            "enabled" => true
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf);

        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'name');
        $expectedConf = [
            0 => "identifier_config",
            "flexible_entity_name" => "Pim/Catalog/Product",
            "data_name" => "name",
            "label" => "Name",
            "enabled" => true
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf);

        $this->configure();
    }

    function it_cannot_handle_misconfigured_attribute_type(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $sku->isUseableAsGridFilter()->willReturn(true);
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->getLabel()->willReturn('Sku');
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->getLabel()->willReturn('Name');

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(array('filter' => array('identifier_config')));
        $registry->getConfiguration('pim_catalog_text')->willReturn(array());

        $this->shouldThrow('\LogicException')->duringConfigure();
    }
}
