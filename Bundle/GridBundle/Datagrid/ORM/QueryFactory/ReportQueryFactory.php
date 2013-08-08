<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;

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
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
