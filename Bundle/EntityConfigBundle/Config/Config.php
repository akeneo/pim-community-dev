<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

/**
 * Object of this class contain meta properties for Configurable Entities and his fields
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
     * @param ConfigIdInterface $id
     */
    public function __construct(ConfigIdInterface $id)
    {
        $this->id = $id;
    }

    /**
     * @return ConfigIdInterface
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     * @param bool   $strict
     * @throws RuntimeException
     * @return mixed|null
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
     * @param string $code
     * @return bool
     */
    public function has($code)
    {
        return isset($this->values[$code]);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function is($code)
    {
        return (bool) $this->get($code);
    }

    /**
     * @param callable $filter
     * @return array
     */
    public function all(\Closure $filter = null)
    {
        return $filter ? array_filter($this->values, $filter) : $this->values;
    }

    /**
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
     * Clone Config
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
