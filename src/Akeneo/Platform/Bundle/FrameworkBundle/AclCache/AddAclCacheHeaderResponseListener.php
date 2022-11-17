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
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AddAclCacheHeaderResponseListener
{
    private const HEADER_ACl_CACHE = 'x-akeneo-acl-cache';

    public function __construct(
        private SampleRouterCache $cache
    ) {
    }

    public function injectAkeneoAclCacheHeader(ResponseEvent $event): void
    {
        if ($this->cache->providerToTarget === null) {
            $value = 'acl_cache_not_used';
        } else if ($this->cache->providerToTarget instanceof ArrayCache) {
            $value = 'array_cache';
        } else {
            $value = 'memcache';
        }
        $event->getResponse()->headers->set(
            self::HEADER_ACl_CACHE,
            $value
        );
    }
}
