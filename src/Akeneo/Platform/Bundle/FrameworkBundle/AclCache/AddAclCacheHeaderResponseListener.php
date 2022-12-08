<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\AclCache;

use Akeneo\Connectivity\Connection\Application\Apps\Security\FindCurrentAppIdInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\AclCache\SampleRouterCache;
use Akeneo\Platform\Bundle\FrameworkBundle\Logging\BoundedContextResolver;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AddAclCacheHeaderResponseListener
{
    private const HEADER_ACl_CACHE = 'x-akeneo-acl-cache';

    public function __construct(
        private CacheProvider $cache
    ) {
    }

    public function injectAkeneoAclCacheHeader(ResponseEvent $event): void
    {
        $stats = $this->cache->getStats();
        $cacheHits= $stats[Cache::STATS_HITS] ?? 0;
        $cacheMiss= $stats[Cache::STATS_MISSES] ?? 0;
        if ($cacheHits === 0 && $cacheMiss === 0) {
            $value = 'acl_cache_not_used';
        } else {
            $value = 'array_cache';
        }

        $event->getResponse()->headers->set(
            self::HEADER_ACl_CACHE,
            $value
        );
    }
}
