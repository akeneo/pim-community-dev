<?php

namespace Oro\Bundle\SecurityBundle\Acl\Cache;

use Doctrine\Common\Cache\CacheProvider;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Cache class for Object Identity ancestors
 */
class OidAncestorsCache
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @param CacheProvider $cache
     */
    public function __construct(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param ObjectIdentity $oid
     * @return bool|array
     */
    public function getAncestorsFromCache(ObjectIdentity $oid)
    {
        return unserialize($this->cache->fetch($this->getObjectIdentityStringId($oid)));
    }

    /**
     * @param ObjectIdentity $oid
     * @param $ancestors
     * @return bool
     */
    public function putAncestorsInCache(ObjectIdentity $oid, $ancestors)
    {
        return $this->cache->save($this->getObjectIdentityStringId($oid), serialize($ancestors));
    }

    /**
     * @param ObjectIdentity $oid
     * @return bool
     */
    public function removeAncestorsFromCache(ObjectIdentity $oid)
    {
        return $this->cache->delete($this->getObjectIdentityStringId($oid));
    }

    /**
     * @return bool
     */
    public function removeAll()
    {
        return $this->cache->deleteAll();
    }

    /**
     * @param ObjectIdentity $oid
     * @return string
     */
    protected function getObjectIdentityStringId(ObjectIdentity $oid)
    {
        return $oid->getIdentifier() . $oid->getType();
    }
}
