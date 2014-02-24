<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $this->beConstructedWith($configuration, $registry, 'Pim/Catalog/Product');
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface');
    }

    function it_configures_datagrid_filters(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'useableAsGridFilter' => 1,
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'useableAsGridFilter' => 1,
                'attributeType' => 'pim_catalog_text'
            ]
        ];
        $configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH)->willReturn($attributes);

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
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->willReturn($configuration);
        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'name');
        $expectedConf = [
            0 => "text_config",
            "flexible_entity_name" => "Pim/Catalog/Product",
            "data_name" => "name",
            "label" => "Name",
            "enabled" => false
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->willReturn($configuration);

        $this->configure();
    }

    function it_cannot_handle_misconfigured_attribute_type(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'useableAsGridFilter' => 1,
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'useableAsGridFilter' => 1,
                'attributeType' => 'pim_catalog_text'
            ]
        ];
        $configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH)->willReturn($attributes);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(array('filter' => array('identifier_config')));
        $registry->getConfiguration('pim_catalog_text')->willReturn(array());

        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'sku');
        $expectedConf = [
            0 => "identifier_config",
            "flexible_entity_name" => "Pim/Catalog/Product",
            "data_name" => "sku",
            "label" => "Sku",
            "enabled" => true
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->willReturn($configuration);
        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'name');
        $expectedConf = [
            0 => "text_config",
            "flexible_entity_name" => "Pim/Catalog/Product",
            "data_name" => "name",
            "label" => "Name",
            "enabled" => false
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->willReturn($configuration);

        $this->shouldThrow('\LogicException')->duringConfigure();
    }
}
