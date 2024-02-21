<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Prophecy\Argument;

class SortersConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $attributes = [
            'sku' => [
                'code' => 'sku',
                'type' => 'pim_catalog_identifier',
            ],
            'name' => [
                'code' => 'name',
                'type' => 'pim_catalog_text',
            ]
        ];

        $configuration
            ->offsetGetByPath(sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY))
            ->willReturn($attributes);

        $configuration
            ->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY))
            ->willReturn(['family' => ['family_config'], 'identifier' => [], 'name' => []]);

        $registry
            ->getConfiguration('pim_catalog_identifier')
            ->willReturn(['column' => ['identifier_config'], 'sorter' => 'flexible_field']);

        $this->beConstructedWith($registry);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement(ConfiguratorInterface::class);
    }

    function it_configures_datagrid_sorters($configuration, $registry)
    {
        $registry
            ->getConfiguration('pim_catalog_text')
            ->willReturn(['column' => ['text_config'], 'sorter' => 'flexible_field']);

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'identifier');
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

        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'identifier');
        $configuration->offsetSetByPath($columnConfPath, Argument::any())->shouldBeCalled();

        $this->shouldThrow('\LogicException')->duringConfigure($configuration);
    }
}
