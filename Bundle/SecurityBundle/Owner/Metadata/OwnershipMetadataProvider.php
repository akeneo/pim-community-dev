<?php

namespace Oro\Bundle\SecurityBundle\Owner\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;

/**
 * This class provides access to the ownership metadata of a domain object
 */
class OwnershipMetadataProvider
{
    /**
     * @var string
     */
    protected $organizationClass;

    /**
     * @var string
     */
    protected $businessUnitClass;

    /**
     * @var string
     */
    protected $userClass;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var OwnershipMetadata
     */
    protected $noOwnershipMetadata;

    /**
     * Constructor
     *
     * @param array $owningEntityNames
     * @param EntityClassResolver $entityClassResolver
     * @param CacheProvider|null $cache
     */
    public function __construct(
        array $owningEntityNames,
        EntityClassResolver $entityClassResolver = null,
        CacheProvider $cache = null
    ) {
        $this->organizationClass = $entityClassResolver === null
            ? $owningEntityNames['organization']
            : $entityClassResolver->getEntityClass($owningEntityNames['organization']);
        $this->businessUnitClass = $entityClassResolver === null
            ? $owningEntityNames['business_unit']
            : $entityClassResolver->getEntityClass($owningEntityNames['business_unit']);
        $this->userClass = $entityClassResolver === null
            ? $owningEntityNames['user']
            : $entityClassResolver->getEntityClass($owningEntityNames['user']);

        $this->cache = $cache;

        $this->noOwnershipMetadata = new OwnershipMetadata();
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
     * Gets the class name of the organization entity
     *
     * @return string
     */
    public function getOrganizationClass()
    {
        return $this->organizationClass;
    }

    /**
     * Gets the class name of the business unit entity
     *
     * @return string
     */
    public function getBusinessUnitClass()
    {
        return $this->businessUnitClass;
    }

    /**
     * Gets the class name of the user entity
     *
     * @return string
     */
    public function getUserClass()
    {
        return $this->userClass;
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
