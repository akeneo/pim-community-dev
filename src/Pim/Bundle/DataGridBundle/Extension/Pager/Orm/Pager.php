<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager\Orm;

use Oro\Bundle\DataGridBundle\Extension\Pager\Orm\Pager as OroPager;
use Akeneo\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;

/**
 * Our custom pager to disable the use of acl helper
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pager extends OroPager
{
    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        $qb    = clone $this->getQueryBuilder();
        $query = $qb->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy')
            ->getQuery();

        return QueryCountCalculator::calculateCount($query);
    }
}
