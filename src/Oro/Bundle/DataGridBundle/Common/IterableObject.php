<?php

namespace Oro\Bundle\DataGridBundle\Common;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class IterableObject implements \ArrayAccess, \IteratorAggregate
{
    const NAME_KEY = 'name';

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var array */
    protected $params;

    protected function __construct(array $params)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->params = $params;
    }

    /**
     * Creates object from array
     *
     * @param array $params
     *
     * @return $this
     */
    public static function create(array $params)
    {
        return new static($params);
    }

    /**
     * Creates object from array, add name as regular param option
     *
     *
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public static function createNamed($name, array $params)
    {
        $params[self::NAME_KEY] = $name;

        return new static($params);
    }

    /**
     * Return Object name
     * throws exception if current object is unnamed
     *
     * @throws \LogicException
     * @return string
     */
    public function getName()
    {
        if (!isset($this[self::NAME_KEY])) {
            throw new \LogicException("Trying to get name of unnamed object");
        }

        return $this[self::NAME_KEY];
    }

    /**
     * Returns param array
     * If keys specified returns only intersection
     *
     * @param array $keys
     * @param array $excludeKeys
     *
     * @return array
     */
    public function toArray(array $keys = [], array $excludeKeys = [])
    {
        $params = $this->params;

        if (!empty($keys)) {
            $params = array_intersect_key($params, array_flip($keys));
        }

        if (!empty($excludeKeys)) {
            $params = array_diff_key($params, array_flip($excludeKeys));
        }

        return $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->params);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->params[$offset];
    }

    /**
     * Try to get property or return default value
     *
     * @param string $offset
     * @param null   $default
     *
     * @return mixed
     */
    public function offsetGetOr($offset, $default = null)
    {
        return isset($this[$offset]) ? $this[$offset] : $default;
    }

    /**
     * Try to get property using PropertyAccessor
     *
     * @param string $path
     * @param null   $default
     *
     * @return mixed
     */
    public function offsetGetByPath($path, $default = null)
    {
        return $this->accessor->getValue($this, $path) ? : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->params[$offset] = $value;

        return $this;
    }

    /**
     * Set property using PropertyAccessor
     *
     * @param string $path
     * @param mixed  $value
     *
     * @return $this
     */
    public function offsetSetByPath($path, $value)
    {
        $this->accessor->setValue($this->params, $path, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);

        return $this;
    }

    /**
     * Merge additional params
     *
     * @param array $params
     *
     * @return $this
     */
    public function merge(array $params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Merge value to array property, if property not isset creates new one
     *
     * @param string $offset
     * @param array  $value
     *
     * @return $this
     */
    public function offsetAddToArray($offset, array $value)
    {
        $this[$offset] = isset($this[$offset]) && is_array($this[$offset]) ? $this[$offset] : [];
        $this[$offset] = array_merge($this[$offset], $value);

        return $this;
    }

    /**
     * Merge value to array property, if property not isset creates new one
     *
     * @param  string $path
     * @param array   $value
     *
     * @return $this
     */
    public function offsetAddToArrayByPath($path, array $value)
    {
        $oldValue = $this->offsetGetByPath($path, []);
        $this->offsetSetByPath($path, array_merge($oldValue, $value));

        return $this;
    }

    /**
     * Validation self using configuration tree definition
     *
     * @param ConfigurationInterface $configuration
     *
     * @return $this
     */
    public function validateConfiguration(ConfigurationInterface $configuration)
    {
        $processor = new Processor();
        $this->params = $processor->processConfiguration($configuration, $this->toArray());

        return $this;
    }
}
