<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\FlexibleFieldProperty;

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
     * @param array
     */
    protected $attributes;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param array                 $attributes    the attributes
     */
    public function __construct(DatagridConfiguration $configuration, $attributes)
    {
        $this->configuration = $configuration;
        $this->attributes    = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $propertiesColumns = $this->configuration->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY));
        $identifierColumn  = array();
        $attributesColumns = array();

        foreach ($this->attributes as $attributeCode => $attribute) {
            $showColumn = $attribute->isUseableAsGridColumn();
            $attributeType = $attribute->getAttributeType();
            // TODO: to fix
            if (in_array($attributeType, array('pim_catalog_file', 'pim_catalog_image'))) {
                continue;
            }
            if ($showColumn) {

                $columnConfig = array(
                    FlexibleFieldProperty::TYPE_KEY         => 'flexible_field',
                    FlexibleFieldProperty::BACKEND_TYPE_KEY => $attribute->getBackendType(),
                    'label'                                 => $attribute->getLabel(),
                );

                if ($attributeType === 'pim_catalog_identifier') {
                    $identifierColumn[$attributeCode]= $columnConfig;
                } else {
                    $attributesColumns[$attributeCode]= $columnConfig;
                }
            }
        }

        uasort(
            $attributesColumns,
            function($col1, $col2) {
                return strcmp($col1['label'], $col2['label']);
            }
        );

        $columns = $identifierColumn + $propertiesColumns + $attributesColumns;
        $this->configuration->offsetSetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $columns);
    }
}
