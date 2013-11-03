<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

class QueryFactory extends AbstractQueryFactory
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder = null)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return ProxyQueryInterface
     * @throws \LogicException
     */
    public function createQuery()
    {
        if (!$this->queryBuilder) {
            throw new \LogicException('Can\'t create datagrid query. Query builder is not configured.');
        }

        return new ProxyQuery($this->queryBuilder);
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
