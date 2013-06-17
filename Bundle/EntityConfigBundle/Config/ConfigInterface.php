<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

interface ConfigInterface extends \Serializable
{
    public function getValues(\Closure $filter = null);
}
