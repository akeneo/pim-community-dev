<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

/**
 * Overriding of boolean filter to filter by the product completeness
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductCompletenessFilter extends BooleanFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $this->util->applyFilter($ds, 'completeness', Operators::EQUALS_ON_AT_LEAST_ONE_LOCALE, 100);
                break;
            case BooleanFilterType::TYPE_NO:
                $this->util->applyFilter($ds, 'completeness', Operators::LOWER_THAN_ON_AT_LEAST_ONE_LOCALE, 100);
                break;
        }

        return true;
    }
}
