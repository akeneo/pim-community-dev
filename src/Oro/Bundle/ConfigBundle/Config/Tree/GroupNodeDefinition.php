<?php

namespace Oro\Bundle\ConfigBundle\Config\Tree;

class GroupNodeDefinition extends AbstractNodeDefinition implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected $children = [];

    /** @var int */
    protected $level = 0;

    public function __construct($name, $definition = [], $children = [])
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
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Getter for nesting level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $this->resort();

        return new \ArrayIterator($this->children);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return !$this->children;
    }

    /**
     * Returns first child
     *
     * @return AbstractNodeDefinition
     */
    public function first()
    {
        $this->resort();

        return reset($this->children);
    }

    /**
     * Resort children array
     *
     * @return void
     */
    public function resort()
    {
        usort(
            $this->children,
            function (AbstractNodeDefinition $a, AbstractNodeDefinition $b) {
                return $a->getPriority() > $b->getPriority() ? -1 : 1;
            }
        );
    }

    /**
     * Retrieve block config from group node definition
     *
     * @return array
     */
    public function toBlockConfig()
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
     *
     * @return array
     */
    public function toViewData()
    {
        return array_intersect_key($this->definition, array_flip(['title', 'priority', 'description', 'icon']));
    }
}
