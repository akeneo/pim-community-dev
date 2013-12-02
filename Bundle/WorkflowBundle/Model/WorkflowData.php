<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityNotFoundException;

class WorkflowData implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var bool
     */
    protected $modified;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
        $this->modified = false;
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * Set value
     *
     * @param string $name
     * @param mixed $value
     * @return WorkflowData
     */
    public function set($name, $value)
    {
        if (!array_key_exists($name, $this->data) || $this->data[$name] !== $value) {
            $this->data[$name] = $value;
            $this->modified = true;
        }
        return $this;
    }

    /**
     * Add values
     *
     * @param array $data
     * @return WorkflowData
     */
    public function add(array $data)
    {
        foreach ($data as $name => $value) {
            $this->set($name, $value);
        }
        return $this;
    }

    /**
     * Get value
     *
     * @param string $name
     * @return mixed $value
     */
    public function get($name)
    {
        if (isset($this->data[$name])) {
            $value = $this->data[$name];
            if ($value instanceof Proxy && !$value->__isInitialized()) {
                // set value as null if entity is not exist
                $this->set($name, $this->extractProxyEntity($value));
            }
            return $this->data[$name];
        } else {
            return null;
        }
    }

    /**
     * Get data values
     *
     * @param array $names Optional list of names of values that should be filtered
     * @return array
     */
    public function getValues(array $names = array())
    {
        if (!$names) {
            return $this->data;
        }

        $result = array();

        /** @var Attribute $attribute */
        foreach ($names as $name) {
            $result[$name] = $this->get($name);
        }

        return $result;
    }

    /**
     * Has value
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Remove value by name
     *
     * @param string $name
     * @return WorkflowData
     */
    public function remove($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
            $this->modified = true;
        }
        return $this;
    }

    /**
     * Is data empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->remove($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @param Proxy $entity
     * @return object|Proxy|null
     */
    protected function extractProxyEntity(Proxy $entity)
    {
        try {
            $entity->__load();
        } catch (EntityNotFoundException $exception) {
            $entity = null;
        }

        return $entity;
    }
}
