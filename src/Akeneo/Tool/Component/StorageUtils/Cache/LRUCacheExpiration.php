<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

/**
 * Periodically clear the cache after a certain period of time.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCacheExpiration implements LRUCacheInterface
{
    private LRUCacheInterface $cache;
    private int $maxLifetime;
    private int $expiry;

    /**
     * @param int $maxLifetime Lifetime of the cache between each clear, in seconds.
     */
    public function __construct(LRUCacheInterface $cache, int $maxLifetime = 300)
    {
        $this->cache = $cache;
        $this->maxLifetime = $maxLifetime;
        $this->expiry = time() + $maxLifetime;
    }

    public function getForKeys(array $keys, callable $queryNotFoundKeys): array
    {
        $this->checkExpiry();

        return $this->cache->getForKeys($keys, $queryNotFoundKeys);
    }

    public function getForKey(string $key, callable $queryNotFoundKey)
    {
        $this->checkExpiry();

        return $this->cache->getForKey($key, $queryNotFoundKey);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    private function checkExpiry(): void
    {
        $time = time();
        if ($time > $this->expiry) {
            $this->expiry = time() + $this->maxLifetime;
            $this->clear();
        }
    }
}
