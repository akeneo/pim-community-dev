<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

interface CacheInterface
{
    /**
     * @param $className
     * @param $scope
     * @return EntityConfig|null
     */
    public function loadConfigFromCache($className, $scope);

    /**
     * @param EntityConfig $config
     */
    public function putConfigInCache(EntityConfig $config);

    /**
     * @param $className
     * @param $scope
     */
    public function removeConfigFromCache($className, $scope);
}
