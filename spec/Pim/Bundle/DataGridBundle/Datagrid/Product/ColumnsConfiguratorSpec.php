<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
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

        $this->beConstructedWith($configuration, $registry);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }

    function it_configures_datagrid_columns($configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'useableAsGridColumn' => 1,
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'useableAsGridColumn' => 1,
                'attributeType' => 'pim_catalog_text'
            ],
            'desc' => [
                'code'  => 'desc',
                'label' => 'Desc',
                'useableAsGridColumn' => 0,
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
            'name' => [
                'text_config',
                'label' => 'Name'
            ]
        ];

        $displayedColumns = $availableColumns;
        array_pop($displayedColumns);

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $configuration->offsetSetByPath($columnConfPath, $displayedColumns)->shouldBeCalled();

        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $configuration->offsetSetByPath($availableColumnPath, $availableColumns)->shouldBeCalled();

        $this->configure();
    }

    function it_doesnt_add_column_for_not_useable_as_column_attribute($configuration, $registry)
    {
        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $columns = [
            'family' => [
                'family_config',
            ],
        ];

        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'useableAsGridColumn' => 0,
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'useableAsGridColumn' => 0,
                'attributeType' => 'pim_catalog_text'
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($displayColumnPath)->shouldBeCalled();

        $configuration->offsetSetByPath($columnConfPath, $columns)->shouldBeCalled();

        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $configuration->offsetSetByPath($availableColumnPath, $columns)->shouldBeCalled();

        $this->configure();
    }

    function it_displays_only_columns_configured_by_the_user($configuration, $registry)
    {
        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'useableAsGridColumn' => 1,
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'useableAsGridColumn' => 1,
                'attributeType' => 'pim_catalog_text'
            ],
            'desc' => [
                'code'  => 'desc',
                'label' => 'Desc',
                'useableAsGridColumn' => 0,
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
            ]
        ];
        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $configuration->offsetSetByPath($availableColumnPath, $columns)->shouldBeCalled();

        $this->configure();
    }

    function it_cannot_handle_misconfigured_attribute_type($configuration, $registry)
    {
        $registry->getConfiguration('pim_catalog_text')->willReturn(array());

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

        $attributes = [
            'sku' => [
                'code'  => 'sku',
                'label' => 'Sku',
                'useableAsGridColumn' => 0,
                'attributeType' => 'pim_catalog_identifier'
            ],
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'useableAsGridColumn' => 0,
                'attributeType' => 'pim_catalog_text'
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $this->shouldThrow('\LogicException')->duringConfigure();
    }
}
