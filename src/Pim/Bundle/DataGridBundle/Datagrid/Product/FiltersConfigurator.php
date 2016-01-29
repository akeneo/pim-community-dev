<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Filters configurator for product grid
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
     * @param ConfigurationRegistry $registry the conf registry
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
        $attributes = $this->configuration
            ->offsetGet(ContextConfigurator::SOURCE_KEY)[ContextConfigurator::USEABLE_ATTRIBUTES_KEY];
        $attributes = ($attributes === null) ? [] : $attributes;

        $displayedFilters = [];
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
                    ProductFilterUtility::DATA_NAME_KEY => $attributeCode,
                    'label'                             => $attribute['label'],
                    'enabled'                           => ($attributeType === 'pim_catalog_identifier'),
                    'order'                             => $attribute['sortOrder'],
                    'group'                             => $attribute['group'],
                    'groupOrder'                        => $attribute['groupOrder']
                );

                if ($attributeType === 'pim_catalog_metric') {
                    $filterConfig['family'] = $attribute['metricFamily'];
                }

                $displayedFilters[$attributeCode] = $filterConfig;
            }
        }
        $this->sortFilters($displayedFilters);
        $filters = $this->configuration->offsetGet('filters');

        foreach ($displayedFilters as $attributeCode => $filterConfig) {
            $filters['columns'][$attributeCode] = $filterConfig;
        }

        $this->configuration->offsetSet('filters', $filters);
    }

    /**
     * Sort filters by group and attribute sort order
     *
     * @param array &$filters
     *
     * @return null
     */
    protected function sortFilters(&$filters)
    {
        uasort(
            $filters,
            function ($first, $second) {
                if ($first['groupOrder'] === null || $second['groupOrder'] === null) {
                    return $first['groupOrder'] === $second['groupOrder'] ?
                        0 : ($first['groupOrder'] === null ? 1 : -1);
                }

                if ($first['groupOrder'] === $second['groupOrder']) {
                    return $first['order'] > $second['order'] ? 1 : -1;
                }

                return $first['groupOrder'] > $second['groupOrder'] ? 1 : -1;
            }
        );
    }
}
