<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Cache;

use Akeneo\Tool\Component\StorageUtils\Cache\LRUCacheInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCacheExpirationSpec extends ObjectBehavior
{
    public function let(LRUCacheInterface $cache): void
    {
        $this->beConstructedWith($cache);
    }

    public function it_is_a_lru_cache(): void
    {
        $this->shouldImplement(LRUCacheInterface::class);
    }

    public function it_expires_the_cache_once_the_max_lifetime_is_reached(LRUCacheInterface $cache): void
    {
        $query = function () {
            return null;
        };

        $cache->getForKey('entity_code_1', $query)->shouldBeCalledTimes(3)->willReturn(null);
        $cache->clear()->shouldBeCalledTimes(1);

        $this->beConstructedWith($cache, 2);

        $this->getForKey('entity_code_1', $query);
        $this->getForKey('entity_code_1', $query);
        sleep(3);
        $this->getForKey('entity_code_1', $query);
    }
}
