<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Annotation\Loader\AclAnnotationLoaderInterface;

class AclAnnotationProvider
{
    const CACHE_NAMESPACE = 'AclAnnotation';
    const CACHE_KEY = 'data';

    /**
     * @var AclAnnotationLoaderInterface[]
     */
    protected $loaders = [];

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var AclAnnotationStorage
     */
    protected $storage = null;

    /**
     * Constructor
     *
     * @param CacheProvider $cache
     */
    public function __construct(CacheProvider $cache = null)
    {
        $this->cache = $cache;
        if ($this->cache !== null && $this->cache->getNamespace() === '') {
            $this->cache->setNamespace(self::CACHE_NAMESPACE);
        }
    }

    /**
     * Add new loader
     *
     * @param AclAnnotationLoaderInterface $loader
     */
    public function addLoader(AclAnnotationLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * Gets an annotation by its id
     *
     * @param  string             $id
     * @return AclAnnotation|null AclAnnotation object or null if ACL annotation was not found
     */
    public function findAnnotationById(string $id): ?Acl
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->findById($id);
    }

    /**
     * Gets an annotation bound to the given class/method
     *
     * @param  string             $class
     * @param  string|null        $method
     * @return AclAnnotation|null AclAnnotation object or null if ACL annotation was not found
     */
    public function findAnnotation(string $class, ?string $method = null): ?Acl
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->find($class, $method);
    }

    /**
     * Determines whether the given class/method has an annotation
     *
     * @param  string      $class
     * @param  string|null $method
     */
    public function hasAnnotation(string $class, ?string $method = null): bool
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->has($class, $method);
    }

    /**
     * Gets annotations
     *
     * @param  string|null     $type The annotation type
     * @return AclAnnotation[]
     */
    public function getAnnotations(?string $type = null): array
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->getAnnotations($type);
    }

    /**
     * Checks whether the given class or at least one of its method is protected by ACL security policy
     *
     * @param  string $class
     * @return bool   true if the class is protected; otherwise, false
     */
    public function isProtectedClass(string $class): bool
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->isKnownClass($class);
    }

    /**
     * Checks whether the given method of the given class is protected by ACL security policy
     *
     * @param  string $class
     * @param  string $method
     * @return bool   true if the method is protected; otherwise, false
     */
    public function isProtectedMethod(string $class, string $method): bool
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->isKnownMethod($class, $method);
    }

    /**
     * Warms up the cache
     */
    public function warmUpCache(): void
    {
        $this->ensureAnnotationsLoaded();
    }

    /**
     * Clears the cache
     */
    public function clearCache(): void
    {
        if ($this->cache) {
            $this->cache->delete(self::CACHE_KEY);
        }
        $this->storage = null;
    }

    /**
     * @param  array                $bundleDirectories
     */
    public function getBundleAnnotations(array $bundleDirectories): \Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage
    {
        $data = new AclAnnotationStorage();
        foreach ($this->loaders as $loader) {
            $loader->setBundleDirectories($bundleDirectories);
            $loader->load($data);
        }

        return $data;
    }

    protected function ensureAnnotationsLoaded(): void
    {
        if ($this->storage === null) {
            $data = null;
            if ($this->cache) {
                $data = $this->cache->fetch(self::CACHE_KEY);
            }
            if (!$data) {
                $data = new AclAnnotationStorage();
                foreach ($this->loaders as $loader) {
                    $loader->load($data);
                }

                if ($this->cache) {
                    $this->cache->save(self::CACHE_KEY, $data);
                }
            }

            $this->storage = $data;
        }
    }
}
