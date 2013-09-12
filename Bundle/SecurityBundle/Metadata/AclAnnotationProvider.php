<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\SecurityBundle\Annotation\Loader\AclAnnotationLoaderInterface;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;

class AclAnnotationProvider
{
    /**
     * @var AclAnnotationLoaderInterface[]
     */
    protected $loaders;

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
     * @param AclAnnotationLoaderInterface[] $loaders
     * @param CacheProvider $cache
     */
    public function __construct(array $loaders, CacheProvider $cache = null)
    {
        $this->loaders = $loaders;
        $this->cache = $cache;
    }

    /**
     * Gets an annotation by its id
     *
     * @param string $id
     * @return AclAnnotation|null
     */
    public function findAnnotationById($id)
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->findById($id);
    }

    /**
     * Gets an annotation bound to the given class/method
     *
     * @param $class
     * @param null $method
     * @return AclAnnotation|null
     */
    public function findAnnotation($class, $method = null)
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->find($class, $method);
    }

    /**
     * Gets annotations
     *
     * @param string|null $type The annotation type
     * @return AclAnnotation[]
     */
    public function getAnnotations($type = null)
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->getAnnotations($type);
    }

    /**
     * Checks whether the given class or at least one of its method is protected by ACL security policy
     *
     * @param string $class
     * @return bool true if the class is protected; otherwise, false
     */
    public function isProtectedClass($class)
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->isKnownClass($class);
    }

    /**
     * Checks whether the given method of the given class is protected by ACL security policy
     *
     * @param string $class
     * @param string $method
     * @return bool true if the method is protected; otherwise, false
     */
    public function isProtectedMethod($class, $method)
    {
        $this->ensureAnnotationsLoaded();

        return $this->storage->isKnownMethod($class, $method);
    }

    /**
     * Warms up the cache
     */
    public function warmUpCache()
    {
        $this->ensureAnnotationsLoaded();
    }

    /**
     * Clears the cache
     */
    public function clearCache()
    {
        if ($this->cache) {
            $this->cache->delete('AclAnnotations');
        }
        $this->storage = null;
    }

    protected function ensureAnnotationsLoaded()
    {
        if ($this->storage === null) {
            $data = null;
            if ($this->cache) {
                $data = $this->cache->fetch('AclAnnotations');
            }
            if (!$data) {
                $data = new AclAnnotationStorage();
                foreach ($this->loaders as $loader) {
                    $loader->load($data);
                }

                if ($this->cache) {
                    $this->cache->save('AclAnnotations', $data);
                }
            }

            $this->storage = $data;
        }
    }
}
