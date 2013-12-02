<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Cache\CacheProvider;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;

/**
 * Cache for ConfigInterface
 */
class ConfigCache
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var CacheProvider
     */
    protected $modelCache;

    /**
     * @param CacheProvider $cache
     * @param CacheProvider $modelCache
     */
    public function __construct(CacheProvider $cache, CacheProvider $modelCache)
    {
        $this->cache      = $cache;
        $this->modelCache = $modelCache;
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool|ConfigInterface
     */
    public function loadConfigFromCache(ConfigIdInterface $configId)
    {
        return unserialize($this->cache->fetch($configId->toString()));
    }

    /**
     * @param ConfigInterface $config
     * @return bool
     */
    public function putConfigInCache(ConfigInterface $config)
    {
        return $this->cache->save($config->getId()->toString(), serialize($config));
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool
     */
    public function removeConfigFromCache(ConfigIdInterface $configId)
    {
        return $this->cache->delete($configId->toString());
    }

    /**
     * @return bool
     */
    public function removeAll()
    {
        return $this->cache->deleteAll();
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @return bool|null
     */
    public function getConfigurable($className, $fieldName = null)
    {
        return $this->modelCache->fetch($className . '_' . $fieldName);
    }

    /**
     * @param        $value
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function setConfigurable($value, $className, $fieldName = null)
    {
        return $this->modelCache->save($className . '_' . $fieldName, $value);
    }

    /**
     * @return bool
     */
    public function removeAllConfigurable()
    {
        return $this->modelCache->deleteAll();
    }
}
