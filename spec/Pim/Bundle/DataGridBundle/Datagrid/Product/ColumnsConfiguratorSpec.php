<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

class ColumnsConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $registry->getConfiguration('pim_catalog_identifier')->willReturn(['column' => ['identifier_config']]);
        $registry->getConfiguration('pim_catalog_text')->willReturn(['column' => ['text_config']]);

        $configuration
            ->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY))
            ->willReturn(['family' => ['family_config']]);

        $this->beConstructedWith($registry);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }

    function it_configures_datagrid_columns($configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'attributeType' => 'pim_catalog_text'
            ],
            'desc' => [
                'code'  => 'desc',
                'label' => 'Desc',
                'attributeType' => 'pim_catalog_text'
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($displayColumnPath)->shouldBeCalled();

        $availableColumns = [
            'sku' => [
                'identifier_config',
                'label' => 'Sku'
            ],
            'family' => [
                'family_config',
            ],
            'desc' => [
                'text_config',
                'label' => 'Desc'
            ],
            'name' => [
                'text_config',
                'label' => 'Name'
            ]
        ];

        $displayedColumns = $availableColumns;
        // we don't display the columns coming from the attributes by default
        array_pop($displayedColumns);
        array_pop($displayedColumns);

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $configuration->offsetSetByPath($columnConfPath, $displayedColumns)->shouldBeCalled();

        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $configuration->offsetSetByPath($availableColumnPath, $availableColumns)->shouldBeCalled();

        $this->configure($configuration);
    }

    function it_displays_only_columns_configured_by_the_user($configuration, $registry)
    {
        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'attributeType' => 'pim_catalog_text'
            ],
            'desc' => [
                'code'  => 'desc',
                'label' => 'Desc',
                'attributeType' => 'pim_catalog_text'
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $userColumnsPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($userColumnsPath)->willReturn(array('family', 'sku'));

        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($displayColumnPath)->shouldBeCalled();

        $columns = [
            'sku' => [
                'identifier_config',
                'label' => 'Sku'
            ],
            'family' => [
                'family_config',
            ],
        ];
        $configuration->offsetSetByPath($columnConfPath, $columns)->shouldBeCalled();

        $columns = [
            'sku' => [
                'identifier_config',
                'label' => 'Sku'
            ],
            'family' => [
                'family_config',
            ],
            'name' => [
                'text_config',
                'label' => 'Name'
            ],
            'desc' => [
                'text_config',
                'label' => 'Desc',
            ],
        ];
        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $configuration->offsetSetByPath($availableColumnPath, $columns)->shouldBeCalled();

        $this->configure($configuration);
    }

    function it_cannot_handle_misconfigured_attribute_type($configuration, $registry)
    {
        $registry->getConfiguration('pim_catalog_text')->willReturn(array());

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'attributeType' => 'pim_catalog_text'
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $this->shouldThrow('\LogicException')->duringConfigure($configuration);
    }
}
