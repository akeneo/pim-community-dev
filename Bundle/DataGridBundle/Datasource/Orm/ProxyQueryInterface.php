<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm;

/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface
{
    /**
     * Execute query
     *
     * @param  array $params
     * @param  null  $hydrationMode
     *
     * @return mixed
     */
    public function execute(array $params = array(), $hydrationMode = null);

    /**
     * Proxy for query methods
     *
     * @param  string $name
     * @param  array  $args
     *
     * @return mixed
     */
    public function __call($name, $args);

    /**
     * Adds sorting order
     *
     * @param array  $parentAssociationMappings
     * @param array  $fieldMapping
     * @param string $direction
     */
    public function addSortOrder(array $parentAssociationMappings, array $fieldMapping, $direction = null);

    /**
     * Get records total count
     *
     * @return array
     */
    public function getTotalCount();

    /**
     * Adds select part to internal whitelist
     *
     * @param  string $select
     * @param  bool   $addToWhitelist
     *
     * @return ProxyQueryInterface
     */
    public function addSelect($select = null, $addToWhitelist = false);

    /**
     * Set query parameter
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return ProxyQueryInterface
     */
    public function setParameter($name, $value);

    /**
     * Gets the root alias of the query
     *
     * @return string
     */
    public function getRootAlias();

    /**
     * Sets a query hint
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return ProxyQueryInterface
     */
    public function setQueryHint($name, $value);

    /**
     * Set sort by field
     *
     * @param  array $parentAssociationMappings
     * @param  array $fieldMapping
     *
     * @return ProxyQueryInterface
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping);

    /**
     * Get sort by field
     *
     * @return mixed
     */
    public function getSortBy();

    /**
     * Set sort order
     *
     * @param  mixed $sortOrder
     *
     * @return ProxyQueryInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get sort order
     *
     * @return mixed
     */
    public function getSortOrder();

    /**
     * Get single scalar result
     *
     * @return mixed
     */
    public function getSingleScalarResult();

    /**
     * Set first result
     *
     * @param  int $firstResult
     *
     * @return ProxyQueryInterface
     */
    public function setFirstResult($firstResult);

    /**
     * Get first result
     *
     * @return mixed
     */
    public function getFirstResult();

    /**
     * Set max records
     *
     * @param  int $maxResults
     *
     * @return ProxyQueryInterface
     */
    public function setMaxResults($maxResults);

    /**
     * Get max records
     *
     * @return mixed
     */
    public function getMaxResults();

    /**
     * Get unique parameter ID
     *
     * @return mixed
     */
    public function getUniqueParameterId();

    /**
     * Join entity
     *
     * @param  array $associationMappings
     *
     * @return mixed
     */
    public function entityJoin(array $associationMappings);
}
