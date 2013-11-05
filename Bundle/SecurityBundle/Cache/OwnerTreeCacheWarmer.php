<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;

class OwnerTreeCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var OwnerTreeProvider
     */
    protected $treeProvider;

    /**
     * @param OwnerTreeProvider $treeProvider
     */
    public function __construct(OwnerTreeProvider $treeProvider)
    {
        $this->treeProvider = $treeProvider;
    }

    /**
     * {inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->treeProvider->warmUpCache();
    }

    /**
     * {inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
