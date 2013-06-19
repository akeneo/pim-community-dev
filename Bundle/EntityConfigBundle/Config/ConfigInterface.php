<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

interface ConfigInterface extends \Serializable
{
    /**
     * @return string
     */
    public function getClassName();

    /**
     * Get Config scope
     *
     * @return string
     */
    public function getScope();
}
