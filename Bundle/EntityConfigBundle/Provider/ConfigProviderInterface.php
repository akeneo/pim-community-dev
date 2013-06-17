<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

interface ConfigProviderInterface
{
    /**
     * @param $className
     * @param $scope
     * @return EntityConfig
     */
    public function getConfig($className, $scope);
}
