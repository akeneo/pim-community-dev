<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

class EntityProxyQuery extends ProxyQuery
{
    /**
     * Get query builder for result query
     *
     * @return QueryBuilder
     */
    protected function getResultQueryBuilder()
    {
        $qb = clone $this->getQueryBuilder();

        $this->applyWhere($qb);
        $this->applyOrderByParameters($qb);

        return $qb;
    }

    /**
     * Get query builder for result count query
     *
     * @return QueryBuilder
     */
    protected function getCountQueryBuilder()
    {
        return clone $this->getResultIdsQueryBuilder();
    }

    /**
     * Apply where part on query builder
     *
     * @param QueryBuilder $qb
     */
    protected function applyWhere(QueryBuilder $qb)
    {
        $idx = $this->getResultIds();

        if (count($idx) > 0) {
            $qb->where($qb->expr()->in($this->getIdFieldFQN(), ':ids'))
               ->resetDQLPart('having')
               ->setMaxResults(null)
               ->setFirstResult(null)
               ->setParameter('ids', $idx);

            // Since DQL has been changed, some parameters potentially are not used anymore.
            $this->fixUnusedParameters($qb);
        }
    }

    /**
     * Fetches ids of objects that query builder targets
     *
     * @return array
     */
    protected function getResultIds()
    {
        $idx   = array();
        $query = $this->getResultIdsQueryBuilder()->getQuery();

        $this->applyQueryHints($query);

        $results    = $query->execute(array(), Query::HYDRATE_ARRAY);
        $connection = $this->getQueryBuilder()->getEntityManager()->getConnection();

        foreach ($results as $id) {
            $idx[] = is_int($id[$this->getIdFieldName()])
                ? $id[$this->getIdFieldName()]
                : $connection->quote($id[$this->getIdFieldName()]);
        }

        return $idx;
    }

    /**
     * Creates query builder that selects only id's of result objects
     *
     * @return QueryBuilder
     */
    protected function getResultIdsQueryBuilder()
    {
        $qb = clone $this->getQueryBuilder();

        // Apply orderBy before change select, because it can contain some expressions from select as aliases
        $this->applyOrderByParameters($qb);

        $selectExpressions = array('DISTINCT ' . $this->getIdFieldFQN());
        // We must leave expressions used in having
        $selectExpressions = array_merge($selectExpressions, $this->selectWhitelist);
        $qb->select($selectExpressions);

        // adding of sort by parameters to select
        // TODO move this logic to addOrderBy method after removing of flexible entity
        /** @var $orderExpression Query\Expr\OrderBy */
        foreach ($qb->getDQLPart('orderBy') as $orderExpression) {
            foreach ($orderExpression->getParts() as $orderString) {
                $orderField = trim(str_ireplace(array(' asc', ' desc'), '', $orderString));
                if (!$this->hasSelectItem($qb, $orderField)) {
                    $qb->addSelect($orderField);
                }
            }
        }

        // Since DQL has been changed, some parameters potentially are not used anymore.
        $this->fixUnusedParameters($qb);

        return $qb;
    }

    /**
     * Removes unused parameters from query builder
     *
     * @param QueryBuilder $qb
     */
    protected function fixUnusedParameters(QueryBuilder $qb)
    {
        $dql = $qb->getDQL();
        $usedParameters = array();

        /** @var $parameter \Doctrine\ORM\Query\Parameter */
        foreach ($qb->getParameters() as $parameter) {
            if ($this->dqlContainsParameter($dql, $parameter->getName())) {
                $usedParameters[$parameter->getName()] = $parameter->getValue();
            }
        }

        $qb->setParameters($usedParameters);
    }
}
