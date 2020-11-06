<?php

namespace Oro\Bundle\ConfigBundle\Config\Tree;

abstract class AbstractNodeDefinition
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $definition;

    public function __construct(string $name, array $definition)
    {
        $this->name = $name;
        $this->definition = $this->prepareDefinition($definition);
    }

    /**
     * Getter for name
     */
    public function getName(): string
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
    public function setPriority(int $priority): self
    {
        $this->definition['priority'] = $priority;

        return $this;
    }

    /**
     * Returns node priority
     */
    public function getPriority(): int
    {
        return $this->definition['priority'];
    }

    /**
     * Prepare definition, set default values
     *
     * @param array $definition
     */
    protected function prepareDefinition(array $definition): array
    {
        if (!isset($definition['priority'])) {
            $definition['priority'] = 0;
        }

        return $definition;
    }
}
