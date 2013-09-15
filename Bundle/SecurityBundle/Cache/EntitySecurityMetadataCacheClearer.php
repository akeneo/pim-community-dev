<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;

class EntitySecurityMetadataCacheClearer implements CacheClearerInterface
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
    public function clear($cacheDir)
    {
        $this->provider->clearCache();
    }
}
