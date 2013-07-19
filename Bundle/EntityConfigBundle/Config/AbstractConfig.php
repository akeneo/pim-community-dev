<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

abstract class AbstractConfig implements ConfigInterface
{
    /**
     * @var array
     */
    protected $values = array();

    /**
     * @var array
     */
    protected $serializeValues = array();

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
            throw new RuntimeException(sprintf(
                "Config '%s' for class '%s' in scope '%s' is not found ",
                $code, $this->getClassName(), $this->getScope()
            ));
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
        return (bool) $this->get($code);
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
}
