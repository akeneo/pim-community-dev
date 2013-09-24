<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;

class AclAnnotationCacheClearer implements CacheClearerInterface
{
    /**
     * @var AclAnnotationProvider
     */
    private $provider;

    /**
     * Constructor
     *
     * @param AclAnnotationProvider $provider
     */
    public function __construct(AclAnnotationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {inheritdoc}
     */
    public function clear($cacheDir)
    {
        $this->provider->clearCache();
    }
}
