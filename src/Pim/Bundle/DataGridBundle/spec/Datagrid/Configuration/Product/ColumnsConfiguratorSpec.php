<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;

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
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }

    function it_configures_datagrid_columns($configuration, $registry)
    {
        $attributes = [
            'sku' => [
                'code'          => 'sku',
                'label'         => 'Sku',
                'type'          => 'pim_catalog_identifier',
                'sortOrder'     => 1,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
            'name' => [
                'code'          => 'name',
                'label'         => 'Name',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 2,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
            'desc' => [
                'code'          => 'desc',
                'label'         => 'Desc',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 3,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($displayColumnPath)->shouldBeCalled();

        $availableColumns = [
            'sku' => [
                'identifier_config',
                'label'      => 'Sku',
                'order'      => 1,
                'group'      => 'General',
                'groupOrder' => 1
            ],
            'family' => [
                'family_config',
            ],
            'name' => [
                'text_config',
                'label'      => 'Name',
                'order'      => 2,
                'group'      => 'General',
                'groupOrder' => 1
            ],
            'desc' => [
                'text_config',
                'label'      => 'Desc',
                'order'      => 3,
                'group'      => 'General',
                'groupOrder' => 1
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
                'code'          => 'sku',
                'label'         => 'Sku',
                'type'          => 'pim_catalog_identifier',
                'sortOrder'     => 1,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
            'name' => [
                'code'          => 'name',
                'label'         => 'Name',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 2,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
            'desc' => [
                'code'          => 'desc',
                'label'         => 'Desc',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 3,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $userColumnsPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($userColumnsPath)->willReturn(['family', 'sku']);

        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $configuration->offsetGetByPath($displayColumnPath)->shouldBeCalled();

        $columns = [
            'sku' => [
                'identifier_config',
                'label'      => 'Sku',
                'order'      => 1,
                'group'      => 'General',
                'groupOrder' => 1
            ],
            'family' => [
                'family_config',
            ],
        ];
        $configuration->offsetSetByPath($columnConfPath, $columns)->shouldBeCalled();

        $columns = [
            'sku' => [
                'identifier_config',
                'label'      => 'Sku',
                'order'      => 1,
                'group'      => 'General',
                'groupOrder' => 1
            ],
            'family' => [
                'family_config',
            ],
            'name' => [
                'text_config',
                'label'      => 'Name',
                'order'      => 2,
                'group'      => 'General',
                'groupOrder' => 1
            ],
            'desc' => [
                'text_config',
                'label'      => 'Desc',
                'order'      => 3,
                'group'      => 'General',
                'groupOrder' => 1
            ],
        ];
        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $configuration->offsetSetByPath($availableColumnPath, $columns)->shouldBeCalled();

        $this->configure($configuration);
    }

    function it_cannot_handle_misconfigured_attribute_type($configuration, $registry)
    {
        $registry->getConfiguration('pim_catalog_text')->willReturn([]);

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

        $attributes = [
            'sku' => [
                'code'          => 'sku',
                'label'         => 'Sku',
                'type'          => 'pim_catalog_identifier',
                'sortOrder'     => 1,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
            'name' => [
                'code'          => 'name',
                'label'         => 'Name',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 2,
                'group'         => 'General',
                'groupOrder'    => 1
            ],
        ];
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetGetByPath($path)->willReturn($attributes);

        $this->shouldThrow('\LogicException')->duringConfigure($configuration);
    }
}
