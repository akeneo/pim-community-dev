<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Annotation\Loader\AclAnnotationLoaderInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class AclAnnotationProvider
{
    const CACHE_NAMESPACE = 'AclAnnotation';
    const CACHE_KEY = 'data';

    /** @var AclAnnotationLoaderInterface[] */
    protected array $loaders = [];
    protected ?AdapterInterface $cache = null;
    protected ?AclAnnotationStorage $storage = null;

    public function __construct(?AdapterInterface $cache = null)
    {
        $this->cache = $cache;
    }

    public function addLoader(AclAnnotationLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    public function findAnnotationById(string $id): ?AclAnnotation
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->findById($id);
    }

    /**
     * Gets an annotation bound to the given class/method
     */
    public function findAnnotation(string $class, ?string $method = null): ?AclAnnotation
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->find($class, $method);
    }

    /**
     * Determines whether the given class/method has an annotation
     */
    public function hasAnnotation(string $class, ?string $method = null): bool
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->has($class, $method);
    }

    /**
     * Gets annotations
     *
     * @param string|null $type The annotation type
     *
     * @return AclAnnotation[]
     */
    public function getAnnotations(?string $type = null): array
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->getAnnotations($type);
    }

    /**
     * Checks whether the given class or at least one of its method is protected by ACL security policy
     */
    public function isProtectedClass(string $class): bool
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->isKnownClass($class);
    }

    /**
     * Checks whether the given method of the given class is protected by ACL security policy
     */
    public function isProtectedMethod(string $class, string $method): bool
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->isKnownMethod($class, $method);
    }

    public function warmUpCache(): void
    {
        $this->ensureAnnotationsLoaded();
    }

    public function clearCache()
    {
        if ($this->cache) {
            $this->cache->clear();
        }
        $this->storage = null;
    }

    public function getBundleAnnotations(array $bundleDirectories): AclAnnotationStorage
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
                $data = $this->cache->getItem(self::CACHE_KEY)->get();
            }
            if (!$data) {
                $data = new AclAnnotationStorage();
                foreach ($this->loaders as $loader) {
                    $loader->load($data);
                }

                if ($this->cache) {
                    $item = $this->cache->getItem(self::CACHE_KEY);
                    $item->set($data);
                }
            }

            $this->storage = $data;
        }
    }
}
