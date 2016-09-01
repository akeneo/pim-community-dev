<?php

namespace Oro\Bundle\ConfigBundle\Config\Tree;

abstract class AbstractNodeDefinition
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $definition;

    public function __construct($name, array $definition)
    {
        $this->name = $name;
        $this->definition = $this->prepareDefinition($definition);
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set node priority
     *
     * @param int $priority
     *
     * @return $this
     *
     */
    public function setPriority($priority)
    {
        $this->definition['priority'] = $priority;

        return $this;
    }

    /**
     * Returns node priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->definition['priority'];
    }

    /**
     * Prepare definition, set default values
     *
     * @param array $definition
     *
     * @return array
     */
    protected function prepareDefinition(array $definition)
    {
        if (!isset($definition['priority'])) {
            $definition['priority'] = 0;
        }

        return $definition;
    }
}
