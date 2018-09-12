<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;

/**
 * Allows to hydrate results of query as ResultRecord array or otherwise
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface HydratorInterface
{
    /**
     * Execute the query and hydrate as result record array
     *
     * @param mixed $qb
     * @param array $options
     *
     * @return ResultRecord[]
     */
    public function hydrate($qb, array $options = []);
}
