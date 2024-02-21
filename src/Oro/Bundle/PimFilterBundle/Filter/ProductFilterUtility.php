<?php

namespace Oro\Bundle\PimFilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;

/**
 * Product filter utility
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilterUtility extends BaseFilterUtility
{
    /**
     * {@inheritdoc}
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, string $field, string $operator, $value)
    {
        $ds->getProductQueryBuilder()->addFilter($field, $operator, $value);
    }
}
