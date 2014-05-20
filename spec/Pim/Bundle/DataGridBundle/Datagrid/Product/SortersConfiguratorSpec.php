<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

class SortersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry, ProductManager $manager)
    {
        $attributes = [
            'sku' => [
                'code'          => 'sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'          => 'name',
                'attributeType' => 'pim_catalog_text'
            ]
        ];

        $configuration
            ->offsetGetByPath(sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY))
            ->willReturn($attributes);

        $configuration
            ->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY))
            ->willReturn(['family' => ['family_config'], 'sku' => [], 'name' => []]);

        $registry
            ->getConfiguration('pim_catalog_identifier')
            ->willReturn(['column' => ['identifier_config'], 'sorter' => 'flexible_field']);

        $this->beConstructedWith($registry, $manager);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }

    function it_configures_datagrid_sorters($configuration, $registry)
    {
        $registry
            ->getConfiguration('pim_catalog_text')
            ->willReturn(['column' => ['text_config'], 'sorter' => 'flexible_field']);

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'sku');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'name');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $columnConfPath = sprintf('%s', OrmSorterConfiguration::COLUMNS_PATH);
        $configuration->offsetGetByPath($columnConfPath)->shouldBeCalled();

        $this->configure($configuration);
    }

    function it_cannot_handle_misconfigured_attribute_type($configuration, $registry)
    {
        $registry->getConfiguration('pim_catalog_text')->willReturn([]);

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'sku');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $this->shouldThrow('\LogicException')->duringConfigure($configuration);
    }
}
