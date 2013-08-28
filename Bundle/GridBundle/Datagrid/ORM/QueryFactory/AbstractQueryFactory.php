<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

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
    abstract public function createQuery();
}
