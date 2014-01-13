<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

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
    protected $attributes;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param ConfigurationRegistry $registry      the conf registry
     * @param array                 $attributes    the attributes
     */
    public function __construct(DatagridConfiguration $configuration, ConfigurationRegistry $registry, $attributes)
    {
        $this->configuration = $configuration;
        $this->registry      = $registry;
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
            $showColumn        = $attribute->isUseableAsGridColumn();
            $attributeType     = $attribute->getAttributeType();
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($showColumn && $attributeTypeConf && $attributeTypeConf['column']) {

                $columnConfig = $attributeTypeConf['column'];
                $columnConfig = $columnConfig + array(
                    'label' => $attribute->getLabel(),
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
            function ($col1, $col2) {
                return strcmp($col1['label'], $col2['label']);
            }
        );

        $columns = $identifierColumn + $propertiesColumns + $attributesColumns;
        $this->configuration->offsetSetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $columns);
    }
}
