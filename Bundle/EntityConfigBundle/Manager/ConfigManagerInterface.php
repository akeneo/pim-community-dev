<?php

namespace Oro\Bundle\EntityConfigBundle\Manager;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderInterface;

interface ConfigManagerInterface
{
    /**
     * @return ConfigProviderInterface
     */
    public function getConfigProvider();

    /**
     * @return string
     */
    public function getScope();
}
