<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as SorterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterQualityScoreFilterAndColumn
{
    private FeatureFlag $featureFlag;

    private RequestParameters $requestParams;

    public function __construct(FeatureFlag $featureFlag, RequestParameters $requestParams)
    {
        $this->featureFlag = $featureFlag;
        $this->requestParams = $requestParams;
    }

    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        if ('product-grid' !== $datagridConfiguration->getName()) {
            return;
        }

        if (!$this->featureFlag->isEnabled()) {
            $this->unregisterQualityScoreFilter($datagridConfiguration);
            $this->unregisterQualityScoreColumn($datagridConfiguration);
            $this->unregisterQualityScoreSorter($datagridConfiguration);
            return;
        }
    }

    private function unregisterQualityScoreFilter(DatagridConfiguration $datagridConfiguration): void
    {
        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);
        unset($filters['columns']['data_quality_insights_score']);
        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    private function unregisterQualityScoreColumn(DatagridConfiguration $datagridConfiguration)
    {
        $defaultColumns = $datagridConfiguration->offsetGet(FormatterConfiguration::COLUMNS_KEY);
        $datagridConfiguration->offsetUnset(FormatterConfiguration::COLUMNS_KEY);
        unset($defaultColumns['data_quality_insights_score']);
        $datagridConfiguration->offsetAddToArray(FormatterConfiguration::COLUMNS_KEY, $defaultColumns);
    }

    private function unregisterQualityScoreSorter(DatagridConfiguration $datagridConfiguration)
    {
        $defaultSorters = $datagridConfiguration->offsetGetByPath(SorterConfiguration::COLUMNS_PATH);
        $datagridConfiguration->offsetUnset(SorterConfiguration::COLUMNS_PATH);
        unset($defaultSorters['data_quality_insights_score']);
        $datagridConfiguration->offsetSetByPath(SorterConfiguration::COLUMNS_PATH, $defaultSorters);
    }
}
