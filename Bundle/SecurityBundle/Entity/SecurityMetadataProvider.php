<?php

namespace Oro\Bundle\SecurityBundle\Entity;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class SecurityMetadataProvider
{
    const ACL_SECURITY_TYPE = 'ACL';

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @param ConfigProvider $configProvider
     * @param CacheProvider|null $cache
     */
    public function __construct(
        ConfigProvider $configProvider,
        CacheProvider $cache = null
    ) {
        $this->configProvider = $configProvider;
        $this->cache = $cache;
    }

    /**
     * Get entities lists marked with acl config
     *
     * @param null $type
     * @return array|SecurityMetadata[]
     */
    public function getEntityList($type = null)
    {
        if (!$type) {
            $type = self::ACL_SECURITY_TYPE;
        }
        $result = array();
        if ($this->cache) {
            $result = $this->cache->fetch($type);
        }
        if (!$result) {
            $configs = $this->configProvider->getConfigs();
            foreach ($configs as $config) {
                if ($config->get('type') == $type) {
                    $result[] = new SecurityMetadata(
                        $type,
                        $config->getId()->getClassName(),
                        $config->get('group_name')
                    );
                }
            }
            if ($this->cache) {
                $this->cache->save($type, $result);
            }
        }

        return $result;
    }

    /**
     * Clears the cache by security type
     *
     * If the $type is not specified, clear all cached data
     *
     * @param string|null $type
     */
    public function clearCache($type = null)
    {
        if ($this->cache) {
            if ($type !== null) {
                $this->cache->delete($type);
            } else {
                $this->cache->deleteAll();
            }
        }
    }
}
