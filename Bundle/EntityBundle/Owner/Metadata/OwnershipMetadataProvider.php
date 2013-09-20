<?php

namespace Oro\Bundle\EntityBundle\Owner\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
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
     * @var ConfigProvider
     */
    protected $configProvider;

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
     * @param ConfigProvider $configProvider
     * @param EntityClassResolver $entityClassResolver
     * @param CacheProvider|null $cache
     */
    public function __construct(
        array $owningEntityNames,
        ConfigProvider $configProvider,
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

        $this->configProvider = $configProvider;
        $this->cache = $cache;

        $this->noOwnershipMetadata = new OwnershipMetadata();
    }

    /**
     * Get the ownership related metadata for the given entity
     *
     * @param string $className
     * @return OwnershipMetadata
     */
    public function getMetadata($className)
    {
        $result = null;
        if ($this->cache) {
            $result = $this->cache->fetch($className);
        }
        if (!$result) {
            if ($this->configProvider->hasConfig($className)) {
                $config = $this->configProvider->getConfig($className);
                $result = new OwnershipMetadata(
                    $config->get('owner_type'),
                    $config->get('owner_field_name'),
                    $config->get('owner_column_name')
                );
            }
            if (!$result) {
                $result = new OwnershipMetadata();
            }
            if ($this->cache) {
                $this->cache->save($className, $result);
            }
        }

        return $result;
    }

    /**
     * Clears the ownership metadata cache
     *
     * If the class name is not specifies this method clears all cached data
     *
     * @param string|null $className
     */
    public function clearCache($className = null)
    {
        if ($this->cache) {
            if ($className !== null) {
                $this->cache->delete($className);
            } else {
                $this->cache->deleteAll();
            }
        }
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
}
