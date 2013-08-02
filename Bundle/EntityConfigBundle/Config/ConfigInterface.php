<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;

interface ConfigInterface extends \Serializable
{
    /**
     * @return ConfigIdInterface
     */
    public function getConfigId();

    /**
     * @param       $code
     * @param  bool $strict
     * @return string
     */
    public function get($code, $strict = false);

    /**
     * @param $code
     * @param $value
     * @return string
     */
    public function set($code, $value);

    /**
     * @param $code
     * @return bool
     */
    public function has($code);

    /**
     * @param $code
     * @return bool
     */
    public function is($code);

    /**
     * @param callable $filter
     * @return array
     */
    public function getValues(\Closure $filter = null);

    /**
     * @param $values
     */
    public function setValues($values);
}
