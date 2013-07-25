<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

interface CacheInterface
{
    /**
     * @param $configId
     * @return EntityConfig|null
     */
    public function loadConfigFromCache($configId);

    /**
     * @param                 $configId
     * @param ConfigInterface $config
     * @return
     */
    public function putConfigInCache($configId, ConfigInterface $config);

    /**
     * @param $configId
     */
    public function removeConfigFromCache($configId);
}
