<?php

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;

/**
 * Columns configurator for product grid, first column is identifier, then properties then ordered attributes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnsConfigurator implements ConfiguratorInterface
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var array */
    protected $propertiesColumns;

    /** @var array */
    protected $editableColumns;

    /** @var array */
    protected $primaryColumns;

    /** @var array */
    protected $attributesColumns;

    /** @var array */
    protected $availableColumns;

    /** @var array */
    protected $displayedColumns;

    /**
     * @param ConfigurationRegistry $registry the config registry
     *
     * @throws \LogicException
     */
    public function __construct(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->preparePropertiesColumns();
        $this->prepareAttributesColumns();
        $this->prepareOtherColumns();
        $this->sortColumns();
        $this->addColumns();
    }

    /**
     * Prepare properties columns, ie, the static columns defined in datagrid.yml
     */
    protected function preparePropertiesColumns()
    {
        $this->propertiesColumns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        $this->editableColumns = [];
        $this->primaryColumns = [];

        foreach ($this->propertiesColumns as $columnCode => $columnData) {
            if (isset($columnData['editable'])) {
                $this->editableColumns[$columnCode] = $columnData;
                unset($this->propertiesColumns[$columnCode]);
            } elseif (isset($columnData['primary'])) {
                $this->primaryColumns[$columnCode] = $columnData;
                unset($this->propertiesColumns[$columnCode]);
            }
        }
    }

    /**
     * Prepare dynamic columns, ie columns for attributes
     */
    protected function prepareAttributesColumns()
    {
        $path = sprintf(self::SOURCE_PATH, self::USEABLE_ATTRIBUTES_KEY);
        $attributes = $this->configuration->offsetGetByPath($path);
        $attributes = ($attributes === null) ? [] : $attributes;
        $this->attributesColumns = [];

        foreach ($attributes as $attributeCode => $attribute) {
            $attributeType = $attribute['type'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if (!$attributeTypeConf || !isset($attributeTypeConf['column'])) {
                throw new \LogicException(
                    sprintf(
                        'Attribute type %s must be configured to display attribute %s as grid column',
                        $attributeType,
                        $attributeCode
                    )
                );
            }

            if ($attributeTypeConf && $attributeTypeConf['column']) {
                $columnConfig = $attributeTypeConf['column'];
                $columnConfig = $columnConfig + [
                    'label'      => $attribute['label'],
                    'order'      => $attribute['sortOrder'],
                    'group'      => $attribute['group'],
                    'groupOrder' => $attribute['groupOrder']
                ];

                $this->attributesColumns[$attributeCode] = $columnConfig;
            }
        }

        uasort(
            $this->attributesColumns,
            function ($col1, $col2) {
                return strcmp($col1['label'], $col2['label']);
            }
        );
    }

    /**
     * Sort the columns
     */
    protected function sortColumns()
    {
        $userColumns = $this->configuration->offsetGetByPath(
            sprintf(self::SOURCE_PATH, self::DISPLAYED_COLUMNS_KEY)
        );

        $this->availableColumns = $this->editableColumns + $this->primaryColumns + $this->propertiesColumns +
            $this->attributesColumns;

        if (!empty($userColumns)) {
            $this->displayedColumns = $this->editableColumns  + $this->primaryColumns;

            foreach ($userColumns as $column) {
                if (array_key_exists($column, $this->availableColumns)) {
                    $this->displayedColumns[$column] = $this->availableColumns[$column];
                }
            }
        } else {
            $this->displayedColumns = $this->editableColumns + $this->primaryColumns + $this->propertiesColumns;
        }
    }

    /**
     * Add columns to datagrid configuration
     */
    protected function addColumns()
    {
        $this->configuration->offsetSetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY),
            $this->displayedColumns
        );

        $this->configuration->offsetSetByPath(
            sprintf(self::SOURCE_PATH, self::AVAILABLE_COLUMNS_KEY),
            $this->availableColumns
        );
    }

    private function prepareOtherColumns()
    {
        $otherColumns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::OTHER_COLUMNS_KEY)
        );

        if (null === $otherColumns) {
            return;
        }

        foreach ($otherColumns as $columnCode => $columnData) {
            $this->attributesColumns[$columnCode] = $columnData;
        }
    }
}
