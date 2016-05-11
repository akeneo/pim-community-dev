<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/**
 * Used when you want to apply the filter from the outside, for example from a listener that access the datasource
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatasourceFilterUtilityInterface
{
    /**
     * Applies a filter on a datasource
     *
     * @param DatasourceInterface $ds
     * @param string              $field
     * @param string              $operator
     * @param mixed               $value
     */
    public function filterDatasource(DatasourceInterface $ds, $field, $operator, $value);
}
