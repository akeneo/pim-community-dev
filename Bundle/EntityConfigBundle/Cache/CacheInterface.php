<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\IdInterface;

interface CacheInterface
{
    /**
     * @param IdInterface $configId
     * @return ConfigInterface|null
     */
    public function loadConfigFromCache(IdInterface $configId);

    /**
     * @param ConfigInterface $config
     */
    public function putConfigInCache(ConfigInterface $config);

    /**
     * @param IdInterface $configId
     */
    public function removeConfigFromCache(IdInterface $configId);
}
