<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ContextConfigurator;

class SortersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry, FlexibleManager $manager)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'attributeType' => 'pim_catalog_text'
            ]
        ];
        $this->beConstructedWith($configuration, $registry, $manager);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface');
    }

    function it_configures_datagrid_sorters(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'attributeType' => 'pim_catalog_text'
            ]
        ];
        $configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH)->willReturn($attributes);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(array('column' => array('identifier_config'), 'sorter' => array()));
        $registry->getConfiguration('pim_catalog_text')->willReturn(array('column' => array('text_config'), 'sorter' => array()));

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $configuration->offsetGetByPath($columnConfPath)->willReturn(array('family' => array('family_config'), 'sku' => array(), 'name' => array()));

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'sku');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'name');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $columnConfPath = sprintf('%s', OrmSorterConfiguration::COLUMNS_PATH);
        $configuration->offsetGetByPath($columnConfPath)->shouldBeCalled();

        $this->configure();
    }

    function it_cannot_handle_misconfigured_attribute_type(DatagridConfiguration $configuration, ConfigurationRegistry $registry, Attribute $sku, Attribute $name)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'attributeType' => 'pim_catalog_text'
            ]
        ];
        $configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH)->willReturn($attributes);

        $registry->getConfiguration('pim_catalog_identifier')->willReturn(array('column' => array('identifier_config')));
        $registry->getConfiguration('pim_catalog_text')->willReturn(array());

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $configuration->offsetGetByPath($columnConfPath)->willReturn(array('family' => array('family_config'), 'sku' => array(), 'name' => array()));

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'sku');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $this->shouldThrow('\LogicException')->duringConfigure();
    }
}
