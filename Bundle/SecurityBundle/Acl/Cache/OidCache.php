<?php

namespace Oro\Bundle\SecurityBundle\Acl\Cache;

use Doctrine\Common\Cache\CacheProvider;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class OidCache
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
        $this->cache->setNamespace('acl_ancestor_cache');
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool|ConfigInterface
     */
    public function getAncestorsFromCache(ObjectIdentity $oid)
    {
        return unserialize($this->cache->fetch($oid->getIdentifier() . $oid->getType()));
    }

    /**
     * @param ConfigInterface $config
     * @return bool
     */
    public function putAncestorsInCache(ObjectIdentity $oid, $ancestors)
    {
        return $this->cache->save($oid->getIdentifier() . $oid->getType(), serialize($ancestors));
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool
     */
    public function removeAncestorsFromCache(ObjectIdentity $oid)
    {
        return $this->cache->delete($oid->getIdentifier() . $oid->getType());
    }
/*********************************************/
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
        $key = $className . '_' . $fieldName;
        if ($this->modelCache->contains($key)) {
            return $this->modelCache->fetch($key);
        }

        return null;
    }

    /**
     * @param $value
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function setConfigurable($value, $className, $fieldName = null)
    {
        $key = $className . '_' . $fieldName;

        return $this->modelCache->save($key, $value);
    }

    /**
     * @return bool
     */
    public function removeAllConfigurable()
    {
        return $this->modelCache->deleteAll();
    }
}