<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

/**
 * The aim of this class is to store configuration data for each configurable object (entity or field).
 */
class Config implements ConfigInterface
{
    /**
     * @var ConfigIdInterface
     */
    protected $id;

    /**
     * @var array
     */
    protected $values = array();

    /**
     * Constructor.
     *
     * @param ConfigIdInterface $id
     */
    public function __construct(ConfigIdInterface $id)
    {
        $this->id = $id;
    }

    /**
     * Returns id of an object for which an instance of this class stores configuration data.
     *
     * @return ConfigIdInterface
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets a value of a configuration parameter.
     *
     * @param string $code    The code (name) a configuration parameter
     * @param bool   $strict  Set to true if this method must raise an exception
     *                        when the requested parameter does not exist
     * @return mixed|null     The parameter value of null if the requested parameter does not exist
     *                        and $strict = false
     * @throws RuntimeException When $strict = true and the requested parameter does not exist
     */
    public function get($code, $strict = false)
    {
        if (isset($this->values[$code])) {
            return $this->values[$code];
        }

        if ($strict) {
            throw new RuntimeException(sprintf('Value "%s" for %s', $code, $this->getId()->toString()));
        }

        return null;
    }

    /**
     * Sets a value of the given configuration parameter.
     *
     * @param string $code
     * @param mixed  $value
     * @return $this
     */
    public function set($code, $value)
    {
        $this->values[$code] = $value;

        return $this;
    }

    /**
     * Checks whether a configuration parameter with the given code exists on not.
     *
     * @param string $code
     * @return bool
     */
    public function has($code)
    {
        return isset($this->values[$code]);
    }

    /**
     * Checks id a value of a configuration parameter equals to $value.
     *
     * @param string $code
     * @param mixed  $value
     * @return bool
     */
    public function is($code, $value = true)
    {
        return $this->get($code) === null ? false : $this->get($code) == $value;
    }

    /**
     * Returns parameters is filtered using the given callback function.
     * Returns all parameters if $filter argument is not specified.
     *
     * @param callable|null $filter The callback function to be used to filter parameters
     * @return array
     */
    public function all(\Closure $filter = null)
    {
        return $filter
            ? array_filter($this->values, $filter)
            : $this->values;
    }

    /**
     * Replace all parameters with parameters specified in $values argument.
     *
     * @param array $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->id, $this->values));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->values) = unserialize($serialized);
    }

    /**
     * Creates a new object that is a copy of the current instance.
     */
    public function __clone()
    {
        $this->id     = clone $this->id;
        $this->values = array_map(
            function ($value) {
                return is_object($value) ? clone $value : $value;
            },
            $this->values
        );
    }
}
