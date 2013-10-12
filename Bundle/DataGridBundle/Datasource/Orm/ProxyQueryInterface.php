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
     * @param array  $sorter
     * @param string $direction
     */
    public function addSortOrder($sorter, $direction = null);

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
     * Get single scalar result
     *
     * @return mixed
     */
    public function getSingleScalarResult();

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
