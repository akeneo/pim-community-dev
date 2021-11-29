<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Memcached\Repository;


use Symfony\Component\RateLimiter\LimiterStateInterface;
use Symfony\Component\RateLimiter\Storage\StorageInterface;

final class RateLimiterMemcachedStorage implements StorageInterface
{
    private \Memcached $memcached;

    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    public function save(LimiterStateInterface $limiterState): void
    {
        $expireAfter = $limiterState->getExpirationTime() ?? 0;

        $this->memcached->add(
            $this->formatKey($limiterState->getId()),
            $limiterState,
            $expireAfter
        );
    }

    public function fetch(string $limiterStateId): ?LimiterStateInterface
    {
        $cacheItem = $this->memcached->get($this->formatKey($limiterStateId));
        if($cacheItem instanceof LimiterStateInterface)
        {
            return $cacheItem;
        }

        return null;
    }

    public function delete(string $limiterStateId): void
    {
        $this->memcached->delete($this->formatKey($limiterStateId));
    }

    private function formatKey(string $limiterStateId): string
    {
        return sha1('ratelimit' . $limiterStateId);
    }
}
