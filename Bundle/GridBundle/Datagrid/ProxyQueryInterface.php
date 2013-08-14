<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface extends BaseProxyQueryInterface
{
    /**
     * Adds sorting order
     *
     * @param array $parentAssociationMappings
     * @param array $fieldMapping
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
     * @param string $select
     * @param bool $addToWhitelist
     * @return ProxyQueryInterface
     */
    public function addSelect($select = null, $addToWhitelist = false);

    /**
     * Set query parameter
     *
     * @param string $name
     * @param mixed $value
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
     * @param string $name
     * @param mixed $value
     * @return ProxyQueryInterface
     */
    public function setQueryHint($name, $value);
}
