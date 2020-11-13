<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

/**
 * Least Recently Used Cache
 *
 * A fixed sized cache that removes the element used last when it reaches its
 * size limit.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LRUCacheInterface
{
    /**
     * This methods gets what is stored on cache.
     * When an entry is not in the cache, it call the first callable in order to fetch the missing entries.
     * These entries HAVE TO BE indexed by the key.
     */
    public function getForKeys(array $keys, callable $queryNotFoundKeys): array;

    /**
     * Returns an entry from the cache or call the query to fetch the entry thanks to the callable.
     */
    public function getForKey(string $key, callable $queryNotFoundKey);

    /**
     * Deletes all values in the cache.
     */
    public function clear(): void;
}
