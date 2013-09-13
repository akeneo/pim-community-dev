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
    protected $loaders = array();

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
    }

    /**
     * Add new loader
     *
     * @param AclAnnotationLoaderInterface $loader
     */
    public function addLoader(AclAnnotationLoaderInterface $loader)
    {
        $this->loaders[] = $loader;
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
