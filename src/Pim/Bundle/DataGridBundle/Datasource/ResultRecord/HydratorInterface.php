<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;

/**
 * Allows to hydrate results of query as ResultRecord array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface HydratorInterface
{
    /**
     * Apply the selector on the datasource
     *
     * @param mixed $queryBuilder
     * @param array $options
     *
     * @return ResultRecord[]
     */
    public function hydrate($queryBuilder, $options);
}
