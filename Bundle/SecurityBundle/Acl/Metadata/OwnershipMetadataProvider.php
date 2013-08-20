<?php

namespace Oro\Bundle\SecurityBundle\Acl\Metadata;

use Doctrine\Common\Cache\CacheProvider;

/**
 * This class provides access to the ownership metadata of entity classes
 */
class OwnershipMetadataProvider
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param CacheProvider|null $cache
     */
    public function __construct(CacheProvider $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Get the ownership related metadata for the given entity
     *
     * @param string $entityName
     * @return OwnershipMetadata
     */
    public function getMetadata($entityName)
    {
        $result = null;
        if ($this->cache) {
            $result = $this->cache->fetch($entityName);
        }
        if ($result === null) {
            if ($this->cache) {
                $this->cache->save($entityName, $result);
            }
        }

        return $result;
    }

    /**
     * Clears the ownership metadata cache
     */
    public function clearCache()
    {
        if ($this->cache) {
            $this->cache->deleteAll();
        }
    }
}
