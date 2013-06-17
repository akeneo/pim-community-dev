<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

abstract class AbstractQueryFactory implements QueryFactoryInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

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
}
