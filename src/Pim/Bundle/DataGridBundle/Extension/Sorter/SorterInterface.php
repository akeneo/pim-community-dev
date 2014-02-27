<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter;

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
     * Prepare and return a closure that is able to apply an order by on a datasource
     *
     * @return \Closure
     */
    public function apply();
}
