<?php
namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Datagrid\IndexerQuery;

// TODO: refactor this class or remove it
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
        return new IndexerQuery(
            $this->indexer,
            $this->indexer->select()
        );
    }
}
