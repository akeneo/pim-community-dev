<?php

namespace Oro\Bundle\WorkflowBundle\Model;

abstract class AbstractAssembler
{
    /**
     * @param array $configuration
     * @return mixed
     */
    abstract public function assemble(array $configuration);

    /**
     * Get entity type
     *
     * @param array $configuration
     * @return string
     */
    protected function getEntityType(array $configuration)
    {
        $keys = array_keys($configuration);
        return $keys[0];
    }

    /**
     * Get entity parameters
     *
     * @param array $configuration
     * @return mixed
     */
    protected function getEntityParameters(array $configuration)
    {
        $values = array_values($configuration);
        return $values[0];
    }

    /**
     * Check that configuration is an entity configuration
     *
     * @param mixed $configuration
     * @return bool
     */
    protected function isService($configuration)
    {
        if (!is_array($configuration) || count($configuration) != 1) {
            return false;
        }
        return strpos($this->getEntityType($configuration), '@') === 0;
    }
}
