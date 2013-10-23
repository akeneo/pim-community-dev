<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Datagrid\IndexerQuery;
use Oro\Bundle\SearchBundle\Query\Query;

class IndexerQueryFactory implements QueryFactoryInterface
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @param Indexer $indexer
     */
    public function __construct(Indexer $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function createQuery()
    {
        new Query();
        return new IndexerQuery(
            $this->indexer,
            $this->indexer->select()
        );
    }
}
