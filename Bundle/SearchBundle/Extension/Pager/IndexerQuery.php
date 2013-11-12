<?php

namespace Oro\Bundle\SearchBundle\Extension\Pager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Engine\Indexer;

/**
 * @method Query setOrderBy() setOrderBy($fieldName, $direction = "ASC", $type = Query::TYPE_TEXT)
 */
class IndexerQuery
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @param Indexer $indexer
     * @param Query   $query
     */
    public function __construct(Indexer $indexer, Query $query)
    {
        $this->indexer = $indexer;
        $this->query   = $query;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->query, $name), $args);
    }

    /**
     * @return Result
     */
    protected function getResult()
    {
        if (!$this->result) {
            $this->result = $this->indexer->query($this->query);
        }

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        return $this->getResult()->getElements();
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->query->setFirstResult($firstResult);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->query->getFirstResult();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->query->setMaxResults($maxResults);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->query->getMaxResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getResult()->getRecordsCount();
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->query->getOrderBy();
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->query->getOrderDirection();
    }
}
