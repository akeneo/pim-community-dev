<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Engine\Indexer;

/**
 * @method Query setOrderBy() setOrderBy($fieldName, $direction = "ASC", $type = Query::TYPE_TEXT)
 */
class IndexerQuery implements ProxyQueryInterface
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

    /**
     * Adds sorting order
     *
     * @param array  $parentAssociationMappings
     * @param array  $fieldMapping
     * @param string $direction
     *
     * @deprecated Can't proxy this method, use setOrderBy instead
     */
    public function addSortOrder(array $parentAssociationMappings, array $fieldMapping, $direction = null)
    {
    }

    /**
     * @param  array $parentAssociationMappings
     * @param  array $fieldMapping
     * @return mixed
     *
     * @deprecated Can't proxy this method, use setOrderBy instead
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
    }

    /**
     * @param  mixed $sortOrder
     * @return void
     *
     * @deprecated Can't proxy this method, use setOrderBy instead
     */
    public function setSortOrder($sortOrder)
    {
    }

    /**
     * @return mixed
     *
     * @deprecated Not allowed for indexer query
     */
    public function getSingleScalarResult()
    {
    }

    /**
     * @return mixed
     *
     * @deprecated Not allowed for indexer query
     */
    public function getUniqueParameterId()
    {
    }

    /**
     * @param  array $associationMappings
     * @return mixed
     *
     * @deprecated Not allowed for indexer query
     */
    public function entityJoin(array $associationMappings)
    {
    }

    /**
     * Set query parameter
     *
     * @param string $name
     * @param mixed  $value
     *
     * @deprecated Not allowed for indexer query
     */
    public function setParameter($name, $value)
    {
    }

    /**
     * Adds select part to internal whitelist
     *
     * @param  string              $select
     * @param  bool                $addToWhitelist
     * @return ProxyQueryInterface
     *
     * @deprecated Not allowed for indexer query
     */
    public function addSelect($select = null, $addToWhitelist = false)
    {
    }

    /**
     * Gets the root alias of the query
     *
     * @return string
     *
     * @deprecated Not allowed for indexer query
     */
    public function getRootAlias()
    {
    }

    /**
     * Sets a query hint
     *
     * @param string $name
     * @param mixed $value
     * @return ProxyQueryInterface
     *
     * @deprecated Not allowed for indexer query
     */
    public function setQueryHint($name, $value)
    {
    }

    /**
     * @return IterableResultInterface
     */
    public function getIterableResult()
    {
    }
}
