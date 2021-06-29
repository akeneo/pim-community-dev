<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $filterDatasource, $data)
    {
        $filterValue = $data['value'] ?? null;

        if (null === $filterValue || ! is_array($filterValue)) {
            return false;
        }

        $this->util->applyFilter($filterDatasource, 'data_quality_insights_score', 'IN', $filterValue);

        return true;
    }
}
