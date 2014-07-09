<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Collector of product value changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ChangesCollectorInterface
{
    /**
     * Store a collected change
     *
     * @param string               $key     The form field key
     * @param array                $changes This data is structured so it can be submitted against a product form
     * @param AbstractProductValue $value   The value
     */
    public function add($key, $changes, AbstractProductValue $value);

    /**
     * Mark a key as removed
     *
     * @param string $key
     */
    public function remove($key);

    /**
     * Get the keys mark as removed
     *
     * @return array
     */
    public function getKeysToRemove();

    /**
     * Get the collected changes
     *
     * @return array
     */
    public function getChanges();
}
