<?php

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;

/**
 * Filters configurator for product grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FiltersConfigurator implements ConfiguratorInterface
{
    /** @var ConfigurationRegistry */
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
        $attributes = $configuration->offsetGet(self::SOURCE_KEY)[self::USEABLE_ATTRIBUTES_KEY];
        $attributes = ($attributes === null) ? [] : $attributes;

        $displayedFilters = [];
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute['code'];
            if (!$attribute['useableAsGridFilter']) {
                continue;
            }

            $attributeType = $attribute['type'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if (!$attributeTypeConf || !isset($attributeTypeConf['filter'])) {
                throw new \LogicException(
                    sprintf(
                        'Attribute type %s must be configured to allow grid filter on attribute %s',
                        $attributeType,
                        $attributeCode
                    )
                );
            }

            $filterConfig = $attributeTypeConf['filter'];
            $filterConfig = $filterConfig + [
                ProductFilterUtility::DATA_NAME_KEY => $attributeCode,
                'label'                             => $attribute['label'],
                'enabled'                           => (AttributeTypes::IDENTIFIER === $attributeType),
                'order'                             => $attribute['sortOrder'],
                'group'                             => $attribute['group'],
                'groupOrder'                        => $attribute['groupOrder']
            ];

            if (AttributeTypes::METRIC === $attributeType) {
                $filterConfig['family'] = $attribute['metricFamily'];
            }

            $displayedFilters[$attributeCode] = $filterConfig;
        }

        $this->sortFilters($displayedFilters);
        $filters = $configuration->offsetGet(FilterConfiguration::FILTERS_KEY);

        foreach ($displayedFilters as $attributeCode => $filterConfig) {
            $filters['columns'][$attributeCode] = $filterConfig;
        }

        $configuration->offsetSet(FilterConfiguration::FILTERS_KEY, $filters);
    }

    /**
     * Sort filters by group and attribute sort order
     *
     * @param array &$filters
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
