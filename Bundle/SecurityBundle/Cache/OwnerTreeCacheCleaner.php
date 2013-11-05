<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;

class OwnerTreeCacheCleaner implements CacheClearerInterface
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
    public function clear($cacheDir)
    {
        $this->treeProvider->clear();
    }
}
