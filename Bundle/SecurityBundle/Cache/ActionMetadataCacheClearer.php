<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;

class ActionMetadataCacheClearer implements CacheClearerInterface
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
    public function clear($cacheDir)
    {
        $this->provider->clearCache();
    }
}
