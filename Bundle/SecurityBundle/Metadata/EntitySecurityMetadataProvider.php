<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class EntitySecurityMetadataProvider
{
    const ACL_SECURITY_TYPE = 'ACL';

    /**
     * @var ConfigProvider
     */
    protected $securityConfigProvider;

    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var array
     *     key = security type
     *     value = array
     *         key = class name
     *         value = EntitySecurityMetadata
     */
    protected $localCache = array();

    /**
     * @param ConfigProvider $securityConfigProvider
     * @param ConfigProvider $entityConfigProvider
     * @param CacheProvider|null $cache
     */
    public function __construct(
        ConfigProvider $securityConfigProvider,
        ConfigProvider $entityConfigProvider,
        CacheProvider $cache = null
    ) {
        $this->securityConfigProvider = $securityConfigProvider;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->cache = $cache;
    }

    /**
     * Checks whether an entity is protected using the given security type.
     *
     * @param string $className The entity class name
     * @param string $securityType The security type. Defaults to ACL.
     * @return bool
     */
    public function isProtectedEntity($className, $securityType = self::ACL_SECURITY_TYPE)
    {
        $this->ensureMetadataLoaded($securityType);

        return isset($this->localCache[$securityType][$className]);
    }

    /**
     * Gets metadata for all entities marked with the given security type.
     *
     * @param string $securityType The security type. Defaults to ACL.
     * @return EntitySecurityMetadata[]
     */
    public function getEntities($securityType = self::ACL_SECURITY_TYPE)
    {
        $this->ensureMetadataLoaded($securityType);

        return array_values($this->localCache[$securityType]);
    }

    /**
     * Clears the cache by security type
     *
     * If the $securityType is not specified, clear all cached data
     *
     * @param string|null $securityType The security type.
     */
    public function clearCache($securityType = null)
    {
        if ($this->cache) {
            if ($securityType !== null) {
                $this->cache->delete($securityType);
            } else {
                $this->cache->deleteAll();
            }
        }
    }

    /**
     * Makes sure that metadata for the given security type is loaded
     *
     * @param string $securityType The security type.
     */
    protected function ensureMetadataLoaded($securityType)
    {
        if (!isset($this->localCache[$securityType])) {
            $data = null;
            if ($this->cache) {
                $data = $this->cache->fetch($securityType);
            }
            if (!$data) {
                $securityConfigs = $this->securityConfigProvider->getConfigs();
                foreach ($securityConfigs as $securityConfig) {
                    if ($securityConfig->get('type') == $securityType) {
                        $className = $securityConfig->getId()->getClassName();
                        $label = '';
                        if ($this->entityConfigProvider->hasConfig($className)) {
                            $label = $this->entityConfigProvider
                                ->getConfig($className)
                                ->get('label');
                        }
                        $data[$className] = new EntitySecurityMetadata(
                            $securityType,
                            $className,
                            $securityConfig->get('group_name'),
                            $label
                        );
                    }
                }
                if ($this->cache) {
                    $this->cache->save($securityType, $data);
                }
            }

            $this->localCache[$securityType] = $data;
        }
    }
}
