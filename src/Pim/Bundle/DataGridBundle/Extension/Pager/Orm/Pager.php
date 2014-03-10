<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager\Orm;

use Oro\Bundle\DataGridBundle\Extension\Pager\Orm\Pager as OroPager;
use Oro\Bundle\DataGridBundle\ORM\Query\QueryCountCalculator;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as PimOrmDatasource;

/**
 * Doctrine ORM pager
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
    public function init()
    {
        $qb = $this->getQueryBuilder();
        if (count($this->getParameters()) > 0) {
            $qb->setParameters($this->getParameters());
        }

        return parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        $qb = clone $this->getQueryBuilder();

        $rootAlias  = $qb->getRootAlias();
        $rootField  = $rootAlias.'.id';
        $qb->groupBy($rootField);

        $qb->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');

        PimOrmDatasource::removeExtraParameters($qb);

        $query = $qb->getQuery();

        return QueryCountCalculator::calculateCount($query);
    }
}
