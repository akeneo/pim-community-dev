<?php

namespace Oro\Bundle\SecurityBundle\AclResource;

use Doctrine\Common\Cache\CacheProvider;

use Oro\Bundle\SecurityBundle\ResourceReader\AnnotationReader;
use Oro\Bundle\SecurityBundle\ResourceReader\ConfigReader;

class AclResourceProvider
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var ConfigReader
     */
    protected $configReader;

    public function __construct(
        AnnotationReader $annotationReader,
        ConfigReader $configReader,
        CacheProvider $cache = null
    ) {
        $this->annotationReader = $annotationReader;
        $this->configReader = $configReader;
        $this->cache = $cache;

        var_dump($this->getAclResourcesFromCode());die;
    }

    protected function getAclResourcesFromCode($directory = '')
    {
        return array_merge(
            $this->annotationReader->getResources($directory),
            $this->configReader->getConfigResources($directory)
        );
    }
}
