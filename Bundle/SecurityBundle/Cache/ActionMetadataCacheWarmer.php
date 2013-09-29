<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;

class ActionMetadataCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ActionMetadataProvider
     */
    private $provider;

    /**
     * Constructor
     *
     * @param ActionMetadataProvider $provider
     */
    public function __construct(ActionMetadataProvider $provider)
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
