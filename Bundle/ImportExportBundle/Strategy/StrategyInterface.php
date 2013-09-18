<?php

namespace Oro\Bundle\ImportExportBundle\Strategy;

/**
 * Interface for import strategies
 */
interface StrategyInterface
{
    /**
     * Process entity according to current strategy
     * Return either updated entity, or null if entity must not be used
     *
     * @param mixed $entity
     * @return mixed|null
     */
    public function process($entity);
}
