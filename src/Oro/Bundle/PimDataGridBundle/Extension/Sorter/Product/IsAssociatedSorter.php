<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;

/**
 * Is associated sorter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsAssociatedSorter implements SorterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        if ($datasource instanceof AssociatedProductDatasource) {
            $datasource->setSortOrder($direction);
        }
    }
}
