<?php

namespace Pim\Component\Resource;

/**
 * Default resource set
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceSet implements ResourceSetInterface
{
    /** @var string */
    protected $type;

    /** @var array */
    protected $resources;

    /** @var int */
    private $position;

    /**
     * @param array  $resources
     * @param string $type
     */
    public function __construct(array $resources, $type)
    {
        $this->type = $type;
        $this->position = 0;
        $this->resources = (array) $resources;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->resources)) {
            return $this->resources[$offset];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (null !== $offset) {
            $this->resources[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->resources)) {
            unset($this->resources[$offset]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->offsetExists($this->position);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
