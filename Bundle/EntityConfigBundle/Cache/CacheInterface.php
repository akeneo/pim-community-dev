<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

interface CacheInterface
{
    /**
     * @param $className
     * @return EntityConfig|null
     */
    public function loadConfigFromCache($className);

    /**
     * @param EntityConfig $config
     */
    public function putConfigInCache(EntityConfig $config);

    /**
     * @param $className
     */
    public function removeConfigFromCache($className);
}
