<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

/**
 * Product filter utility
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilterUtility extends BaseFilterUtility implements DatasourceFilterUtilityInterface
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
     * {@inheritdoc}
     */
    public function filterDatasource(DatasourceInterface $ds, $field, $operator, $value)
    {
        if (!$ds instanceof ProductDatasource) {
            throw new \RuntimeException(sprintf('Expected ProductDatasource, "%s" given ', get_class($ds)));
        }

        $ds->getProductQueryBuilder()->addFilter($field, $operator, $value);
    }
}
