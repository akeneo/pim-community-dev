<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

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
        $attributes = $this->configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH);
        $propertiesColumns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        $identifierColumn  = array();
        $attributesColumns = array();

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
                    $identifierColumn[$attributeCode] = $columnConfig;
                } else {
                    $attributesColumns[$attributeCode] = $columnConfig;
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
