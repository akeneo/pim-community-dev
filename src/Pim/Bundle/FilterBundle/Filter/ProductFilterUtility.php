<?php

namespace Pim\Bundle\FilterBundle\Filter;

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
     * Applies filter to query by attribute
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param string                           $operator
     * @param mixed                            $value
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value)
    {
        $ds->getProductQueryBuilder()->addFilter($field, $operator, $value);
    }

    /**
     * Applies filter to query by attribute
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param mixed                            $value
     * @param string                           $operator
     *
     * @deprecated will be removed in 1.4
     */
    public function applyFilterByAttribute(FilterDatasourceAdapterInterface $ds, $field, $value, $operator)
    {
        $this->applyFilter($ds, $field, $operator, $value);
    }
}
