<?php

namespace Oro\Bundle\SecurityBundle\Acl\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Security\Acl\Domain\DoctrineAclCache;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;

class AclCache extends DoctrineAclCache
{
    protected CacheProvider $cache;

    public function __construct(
        CacheProvider $cache,
        PermissionGrantingStrategyInterface $permissionGrantingStrategy,
        string $prefix = DoctrineAclCache::PREFIX
    ) {
        $this->cache = $cache;
        $this->cache->setNamespace($prefix);
        parent::__construct($this->cache, $permissionGrantingStrategy, $prefix);
    }

    /**
     * {@inheritDoc}
     */
    public function clearCache()
    {
        $this->cache->deleteAll();
    }
}
