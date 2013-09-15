<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;

class EntitySecurityMetadataCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var EntitySecurityMetadataProvider
     */
    private $provider;

    /**
     * Constructor
     *
     * @param EntitySecurityMetadataProvider $provider
     */
    public function __construct(EntitySecurityMetadataProvider $provider)
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
