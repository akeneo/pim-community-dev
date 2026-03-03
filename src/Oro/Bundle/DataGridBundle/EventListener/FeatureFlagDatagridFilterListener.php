<?php

namespace Oro\Bundle\DataGridBundle\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as SorterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

/**
 * In the product grid, there are filters that should only available if the feature is activated.
 */
class FeatureFlagDatagridFilterListener
{
    public function __construct(
        private FeatureFlags $featureFlags
    ) {
    }

    public function filterColumns(BuildBefore $event)
    {
        $datagridConfiguration = $event->getConfig();

        $this->featureFlagOnFilter($datagridConfiguration);
        $this->featureFlagOnDefaultColumns($datagridConfiguration);
        $this->featureFlagOnSorter($datagridConfiguration);
    }

    private function featureFlagOnFilter(DatagridConfiguration $datagridConfiguration): void
    {
        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);

        if (!isset($filters['columns'])) {
            return;
        }

        $filters['columns'] = array_filter($filters['columns'], function ($properties) {
            if (isset($properties['feature_flag']) && !$this->featureFlags->isEnabled($properties['feature_flag'])) {
                return false;
            }

            return true;
        });
        $datagridConfiguration->offsetUnset(Configuration::FILTERS_KEY);
        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    private function featureFlagOnDefaultColumns(DatagridConfiguration $datagridConfiguration)
    {
        $defaultColumns = $datagridConfiguration->offsetGet(FormatterConfiguration::COLUMNS_KEY);

        if (empty($defaultColumns)) {
            return;
        }

        $defaultColumns = array_filter($defaultColumns, function ($properties) {
            if (isset($properties['feature_flag']) && !$this->featureFlags->isEnabled($properties['feature_flag'])) {
                return false;
            }

            return true;
        });

        $datagridConfiguration->offsetUnset(FormatterConfiguration::COLUMNS_KEY);
        $datagridConfiguration->offsetAddToArray(FormatterConfiguration::COLUMNS_KEY, $defaultColumns);
    }

    private function featureFlagOnSorter(DatagridConfiguration $datagridConfiguration)
    {
        $defaultSorters = $datagridConfiguration->offsetGetByPath(SorterConfiguration::COLUMNS_PATH);

        if (empty($defaultSorters)) {
            return;
        }

        $defaultSorters = array_filter($defaultSorters, function ($properties) {
            if (isset($properties['feature_flag']) && !$this->featureFlags->isEnabled($properties['feature_flag'])) {
                return false;
            }

            return true;
        });

        $datagridConfiguration->offsetUnset(SorterConfiguration::COLUMNS_PATH);
        $datagridConfiguration->offsetSetByPath(SorterConfiguration::COLUMNS_PATH, $defaultSorters);
    }
}
