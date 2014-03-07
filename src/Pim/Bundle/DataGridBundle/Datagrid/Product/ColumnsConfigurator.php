<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

/**
 * Columns configurator for flexible grid, first column is identifier, then properties then ordered attributes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnsConfigurator implements ConfiguratorInterface
{
    /**
     * @param DatagridConfiguration
     */
    protected $configuration;

    /**
     * @param ConfigurationRegistry
     */
    protected $registry;

    /**
     * @param array
     */
    protected $propertiesColumns;

    /**
     * @param array
     */
    protected $editableColumns;

    /**
     * @param array
     */
    protected $identifierColumn;

    /**
     * @param array
     */
    protected $attributesColumns;

    /**
     * @param array
     */
    protected $availableColumns;

    /**
     * @param array
     */
    protected $displayedColumns;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param ConfigurationRegistry $registry      the config registry
     *
     * @throws \LogicException
     */
    public function __construct(DatagridConfiguration $configuration, ConfigurationRegistry $registry)
    {
        $this->configuration = $configuration;
        $this->registry      = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->preparePropertiesColumns();
        $this->prepareAttributesColumns();
        $this->sortColumns();
        $this->addColumns();
    }

    /**
     * Prepare properties columns, ie, the static columns defined in datagrid.yml
     *
     * @return null
     */
    protected function preparePropertiesColumns()
    {
        $this->propertiesColumns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        $this->editableColumns = array();

        foreach ($this->propertiesColumns as $columnCode => $columnData) {
            if (isset($columnData['editable'])) {
                $this->editableColumns[$columnCode] = $columnData;
                unset($this->propertiesColumns[$columnCode]);
            }
        }
    }

    /**
     * Prepare dynamic columns, ie columns for attributes
     *
     * @return null
     */
    protected function prepareAttributesColumns()
    {
        $attributes = $this->configuration->offsetGetByPath(ProductDatasource::USEABLE_ATTRIBUTES_PATH);
        $this->identifierColumn  = array();
        $this->attributesColumns = array();

        foreach ($attributes as $attributeCode => $attribute) {
            $showColumn        = $attribute['useableAsGridColumn'];
            $attributeType     = $attribute['attributeType'];
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

            if ($showColumn && $attributeTypeConf && $attributeTypeConf['column']) {
                $columnConfig = $attributeTypeConf['column'];
                $columnConfig = $columnConfig + array(
                    'label' => $attribute['label'],
                );

                if ($attributeType === 'pim_catalog_identifier') {
                    $this->identifierColumn[$attributeCode] = $columnConfig;
                } else {
                    $this->attributesColumns[$attributeCode] = $columnConfig;
                }
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
     *
     * @return null
     */
    protected function sortColumns()
    {
        $userColumns = $this->configuration->offsetGetByPath(
            sprintf('[source][%s]', ContextConfigurator::DISPLAYED_COLUMNS_KEY)
        );

        $this->availableColumns = $this->editableColumns + $this->identifierColumn + $this->propertiesColumns
            + $this->attributesColumns;

        if (!empty($userColumns)) {
            $this->displayedColumns = $this->editableColumns;
            foreach ($userColumns as $column) {
                if (array_key_exists($column, $this->availableColumns)) {
                    $this->displayedColumns[$column] = $this->availableColumns[$column];
                }
            }

        } else {
            $this->displayedColumns = $this->availableColumns;
        }
    }

    /**
     * Add columns to datagrid configuration
     *
     * @return null
     */
    protected function addColumns()
    {
        $this->configuration->offsetSetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY),
            $this->displayedColumns
        );

        $this->configuration->offsetSetByPath(
            sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY),
            $this->availableColumns
        );
    }
}
