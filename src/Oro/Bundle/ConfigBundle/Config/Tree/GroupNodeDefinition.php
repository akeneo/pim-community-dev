<?php

namespace Oro\Bundle\ConfigBundle\Config\Tree;

class GroupNodeDefinition extends AbstractNodeDefinition implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected $children = [];

    /** @var int */
    protected $level = 0;

    public function __construct(string $name, $definition = [], array $children = [])
    {
        parent::__construct($name, $definition);
        $this->children = $children;
    }

    /**
     * Setter for nesting level
     *
     * @param int $level
     *
     * @return $this
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Getter for nesting level
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->children);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \ArrayIterator
    {
        $this->resort();

        return new \ArrayIterator($this->children);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return !$this->children;
    }

    /**
     * Returns first child
     */
    public function first(): AbstractNodeDefinition
    {
        $this->resort();

        return reset($this->children);
    }

    /**
     * Resort children array
     */
    public function resort(): void
    {
        usort(
            $this->children,
            fn(AbstractNodeDefinition $a, AbstractNodeDefinition $b) => $a->getPriority() > $b->getPriority() ? -1 : 1
        );
    }

    /**
     * Retrieve block config from group node definition
     */
    public function toBlockConfig(): array
    {
        return [
            $this->getName() => array_intersect_key(
                $this->definition,
                array_flip(['title', 'priority', 'description'])
            )
        ];
    }

    /**
     * Returns needed definition values to view
     */
    public function toViewData(): array
    {
        return array_intersect_key($this->definition, array_flip(['title', 'priority', 'description', 'icon']));
    }
}
