<?php
namespace Oro\Bundle\AsseticBundle\Controller;

use Symfony\Bundle\AsseticBundle\Controller\AsseticController as BaseController;
use Assetic\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;

use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;

class AsseticController extends BaseController
{
    public function __construct(
        Request $request,
        OroAssetManager $am,
        CacheInterface $cache,
        $enableProfiler = false,
        Profiler $profiler = null
    ) {
        $this->request = $request;
        $this->am = $am;
        $this->cache = $cache;
        $this->enableProfiler = (boolean) $enableProfiler;
        $this->profiler = $profiler;
    }
}
