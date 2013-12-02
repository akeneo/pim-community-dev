<?php

namespace Oro\Bundle\EntityExtendBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class EntityCacheWarmer extends CacheWarmer
{
    /**
     * @var ExtendConfigDumper
     */
    private $dumper;

    /**
     * Constructor.
     *
     * @param ExtendConfigDumper $dumper
     */
    public function __construct(ExtendConfigDumper $dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * {inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->dumper->dump();
    }

    /**
     * {inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
