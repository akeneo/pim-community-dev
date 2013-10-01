<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;

abstract class AbstractQueryFactory implements QueryFactoryInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;
}
