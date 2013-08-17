<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

/**
 * Id of Config
 */
interface ConfigIdInterface extends \Serializable
{
    /**
     * @return string
     */
    public function toString();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return string
     */
    public function getScope();
}
