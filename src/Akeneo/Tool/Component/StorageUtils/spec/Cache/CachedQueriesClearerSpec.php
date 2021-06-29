<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Cache;

use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use PhpSpec\ObjectBehavior;

class CachedQueriesClearerSpec extends ObjectBehavior
{
    function it_clear_all_cached_queries(
        CachedQueryInterface $cachedQuery1,
        CachedQueryInterface $cachedQuery2
    ) {
        $this->beConstructedWith([
            $cachedQuery1,
            $cachedQuery2
        ]);

        $cachedQuery1->clearCache()->shouldBeCalledOnce();
        $cachedQuery2->clearCache()->shouldBeCalledOnce();

        $this->clear();
    }

    function it_throws_an_exception_when_query_is_not_a_cached_query(
        CachedQueryInterface $cachedQuery1,
        \stdClass $LRUCache
    ) {
        $this->beConstructedWith([$cachedQuery1, $LRUCache]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
