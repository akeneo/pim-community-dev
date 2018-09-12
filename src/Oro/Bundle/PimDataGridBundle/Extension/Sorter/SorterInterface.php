<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/**
 * Sorter interface, allows to join extra data in the datasource before to sort on
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SorterInterface
{
    /**
     * Apply a custom order by on the datasource
     *
     * @param DatasourceInterface $datasource
     * @param string              $field
     * @param string              $direction
     */
    public function apply(DatasourceInterface $datasource, $field, $direction);
}
