<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class AclAnnotationCacheWarmer implements CacheWarmerInterface
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
    public function warmUp($cacheDir)
    {
        $this->provider->warmUpCache();
    }

    /**
     * {inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
