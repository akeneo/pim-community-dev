<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

use Webmozart\Assert\Assert;

final class CachedQueriesClearer implements CachedQueriesClearerInterface
{
    /** @var CachedQueryInterface[] */
    private iterable $cachedQueries;

    public function __construct(iterable $cachedQueries)
    {
        Assert::allIsInstanceOf($cachedQueries, CachedQueryInterface::class);

        $this->cachedQueries = $cachedQueries;
    }

    public function clear(): void
    {
        foreach ($this->cachedQueries as $cachedQuery) {
            $cachedQuery->clearCache();
        }
    }
}
