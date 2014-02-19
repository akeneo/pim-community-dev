<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

/**
 * Filters configurator for flexible grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FiltersConfigurator implements ConfiguratorInterface
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
     * @param $string
     */
    protected $flexibleEntity;

    /**
     * @param DatagridConfiguration $configuration  the grid config
     * @param ConfigurationRegistry $registry       the conf registry
     * @param string                $flexibleEntity the flexible entity FQCN
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ConfigurationRegistry $registry,
        $flexibleEntity
    ) {
        $this->configuration  = $configuration;
        $this->registry       = $registry;
        $this->flexibleEntity = $flexibleEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $attributes = $this->configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH);

        foreach ($attributes as $attributeCode => $attribute) {
            $showFilter        = $attribute['useableAsGridFilter'];
            $attributeType     = $attribute['attributeType'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($showFilter && (!$attributeTypeConf || !isset($attributeTypeConf['filter']))) {
                throw new \LogicException(
                    sprintf(
                        'Attribute type %s must be configured to allow grid filter on attribute %s',
                        $attributeType,
                        $attributeCode
                    )
                );
            }

            if ($showFilter && $attributeTypeConf && isset($attributeTypeConf['filter'])) {

                $filterConfig = $attributeTypeConf['filter'];
                $filterConfig = $filterConfig + array(
                    FilterUtility::FEN_KEY       => $this->flexibleEntity,
                    FilterUtility::DATA_NAME_KEY => $attributeCode,
                    'label'                      => $attribute['label'],
                    'enabled'                    => ($attributeType === 'pim_catalog_identifier')
                );

                if ($attributeType === 'pim_catalog_metric') {
                    $filterConfig['family'] = $attribute['metricFamily'];
                }

                $this->configuration->offsetSetByPath(
                    sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $attributeCode),
                    $filterConfig
                );
            }
        }
    }
}
