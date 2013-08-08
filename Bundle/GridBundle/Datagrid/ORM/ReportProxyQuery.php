<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;

/**
 * Extension for report queries - allow aggregation usage
 */
class ReportProxyQuery extends ProxyQuery
{
    /**
     * Get total records count
     *
     * @return int
     */
    public function getTotalCount()
    {
        $qb    = clone $this->getResultQueryBuilder();
        $query = $qb->setFirstResult(null)
           ->setMaxResults(null)
           ->resetDQLPart('orderBy')
           ->getQuery();

        $this->applyQueryHints($query);

        $countCalculator = new CountCalculator();

        return $countCalculator->getCount($query);
    }

    /**
     * Apply where part to query builder
     *
     * @param QueryBuilder $qb
     */
    protected function applyWhere(QueryBuilder $qb)
    {
        // disable parent's ids algorithm here
    }
}
