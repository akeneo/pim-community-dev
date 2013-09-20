<?php

namespace Oro\Bundle\EntityExtendBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Oro\Bundle\EntityExtendBundle\Tools\Generator;

class ExtendCacheWarmer extends CacheWarmer
{
    /**
     * @var Generator
     */
    protected $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
    }
}
