<?php

namespace Oro\Bundle\SecurityBundle\Acl\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Security\Acl\Domain\DoctrineAclCache;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;

class AclCache extends DoctrineAclCache
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @param CacheProvider $cache
     * @param PermissionGrantingStrategyInterface $permissionGrantingStrategy
     * @param string $prefix
     */
    public function __construct(
        CacheProvider $cache,
        PermissionGrantingStrategyInterface $permissionGrantingStrategy,
        $prefix = DoctrineAclCache::PREFIX
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
