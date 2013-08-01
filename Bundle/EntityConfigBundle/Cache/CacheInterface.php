<?php

namespace Oro\Bundle\EntityConfigBundle\Cache;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;

interface CacheInterface
{
    /**
     * @param ConfigIdInterface $configId
     * @return ConfigInterface|null
     */
    public function loadConfigFromCache(ConfigIdInterface $configId);

    /**
     * @param ConfigInterface $config
     */
    public function putConfigInCache(ConfigInterface $config);

    /**
     * @param ConfigIdInterface $configId
     */
    public function removeConfigFromCache(ConfigIdInterface $configId);
}
