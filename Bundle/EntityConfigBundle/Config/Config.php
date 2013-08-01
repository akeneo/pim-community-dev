<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

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
    public function getConfigId()
    {
        return $this->id;
    }

    /**
     * @param                   $code
     * @param  bool             $strict
     * @throws RuntimeException
     * @return string
     */
    public function get($code, $strict = false)
    {
        if (isset($this->values[$code])) {
            return $this->values[$code];
        } elseif ($strict) {
            throw new RuntimeException(sprintf('Value "%s" for %s', $code, $this->getConfigId()));
        }

        return null;
    }

    /**
     * @param $code
     * @param $value
     * @return string
     */
    public function set($code, $value)
    {
        $this->values[$code] = $value;

        return $this;
    }

    /**
     * @param $code
     * @return bool
     */
    public function has($code)
    {
        return isset($this->values[$code]);
    }

    /**
     * @param $code
     * @return bool
     */
    public function is($code)
    {
        return (bool)$this->get($code);
    }

    /**
     * @param  array $exclude
     * @param  array $include
     * @return array
     */
    public function getValues(array $exclude = array(), array $include = array())
    {
        switch (true) {
            case count($exclude):
                return array_diff_key($this->values, array_reverse($exclude));
                break;
            case count($include):
                return array_intersect_key($this->values, array_reverse($exclude));
                break;
            default:
                return $this->values;
        }
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
        return serialize(array(
            $this->id,
            $this->values,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->values,
            ) = unserialize($serialized);
    }

    /**
     * Clone Config
     */
    public function __clone()
    {
        $this->id     = clone $this->id;
        $this->values = array_map(function ($value) {
            if (is_object($value)) {
                return clone $value;
            } else {
                return $value;
            }
        }, $this->values);
    }
}
