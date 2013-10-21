<?php

namespace Oro\Bundle\SecurityBundle\Owner\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class provides access to the ownership metadata of a domain object
 */
class OwnershipMetadataProvider
{
    const CACHE_NAMESPACE = 'EntityOwnership';

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
     * @var array
     *         key = class name
     *         value = OwnershipMetadata or true if an entity has no ownership config
     */
    protected $localCache;

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
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if ($this->cache !== null && $this->cache->getNamespace() === '') {
            $this->cache->setNamespace(self::CACHE_NAMESPACE);
        }

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
        $this->ensureMetadataLoaded($className);

        $result = $this->localCache[$className];
        if ($result === true) {
            return $this->noOwnershipMetadata;
        }

        return $result;
    }

    /**
     * Warms up the cache
     *
     * If the class name is specified this method warms up cache for this class only
     *
     * @param string|null $className
     */
    public function warmUpCache($className = null)
    {
        if ($className === null) {
            foreach ($this->configProvider->getConfigs() as $config) {
                $this->ensureMetadataLoaded($config->getId()->getClassName());
            }
        } else {
            $this->ensureMetadataLoaded($className);
        }
    }

    /**
     * Clears the ownership metadata cache
     *
     * If the class name is not specified this method clears all cached data
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

    /**
     * Makes sure that metadata for the given class are loaded
     *
     * @param string $className
     * @throws InvalidConfigurationException
     */
    protected function ensureMetadataLoaded($className)
    {
        if (!isset($this->localCache[$className])) {
            $data = null;
            if ($this->cache) {
                $data = $this->cache->fetch($className);
            }
            if (!$data) {
                if ($this->configProvider->hasConfig($className)) {
                    $config = $this->configProvider->getConfig($className);
                    try {
                        $data = new OwnershipMetadata(
                            $config->get('owner_type'),
                            $config->get('owner_field_name'),
                            $config->get('owner_column_name')
                        );
                    } catch (\InvalidArgumentException $ex) {
                        throw new InvalidConfigurationException(
                            sprintf('Invalid entity ownership configuration for "%s".', $className),
                            0,
                            $ex
                        );
                    }
                }
                if (!$data) {
                    $data = true;
                }

                if ($this->cache) {
                    $this->cache->save($className, $data);
                }
            }

            $this->localCache[$className] = $data;
        }
    }
}
