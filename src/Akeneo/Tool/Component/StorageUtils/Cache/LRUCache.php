<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

/**
 * Least Recently Used Cache
 *
 * A fixed sized cache that removes the element used last when it reaches its
 * size limit.
 *
 * @see https://github.com/cash/LRUCache
 */
final class LRUCache
{
    /** @var int */
    private $maximumSize;

    /**
     * The front of the array contains the LRU element
     *
     * @var array
     */
    private $data = [];

    /** @var string */
    private $nullData;

    /** @var string */
    private $defaultValue;

    /**
     * Create a LRU Cache
     *
     * @param int $size
     * @throws \InvalidArgumentException
     */
    public function __construct(int $size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException("The size has to be a positive int");
        }
        $this->nullData = sha1('NULL_DATA_ON_LRU_CACHE');
        $this->defaultValue = sha1('DEFAULT_CACHED_VALUE');

        $this->maximumSize = $size;
    }

    /**
     * This methods gets what is stored on cache.
     * When an entry is not in the cache, it call the first callable in order to fetch the missing entries.
     * These entries HAVE TO BE indexed by the key.
     *
     * The performance impact of the callable is non significant.
     *
     * @see the specification to have a concrete example of how to use it.
     */
    public function getForKeys(array $keys, callable $queryNotFoundKeys): array
    {
        $fromCacheIndexedByKey = [];
        $valuesNotFoundKeysInCache = [];

        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($this->defaultValue === $value) {
                $valuesNotFoundKeysInCache[] = $key;
            } else {
                $fromCacheIndexedByKey[$key] = $value;
            }
        }

        $resultFromQuery = [];
        if (count($valuesNotFoundKeysInCache) > 0) {
            $resultFromQuery = $queryNotFoundKeys($valuesNotFoundKeysInCache);
        }

        foreach ($resultFromQuery as $key => $value) {
            $this->put((string) $key, $value);
        }

        return array_replace($resultFromQuery, $fromCacheIndexedByKey);
    }

    /**
     * Returns an entry from the cache or call the query to fetch the entry thanks to the callable.
     *
     * The performance impact of the callable and `array_key_exist` instead of `isset` are non significant.
     * The time consuming tasks are call to `recordAccess` and reset + unset in `put` method.
     */
    public function getForKey(string $key, callable $queryNotFoundKey)
    {
        if (array_key_exists($key, $this->data)) {
            $this->recordAccess($key);

            return $this->data[$key];
        }

        $resultFromQuery = $queryNotFoundKey($key);
        $this->put((string) $key, $resultFromQuery);


        return $resultFromQuery;
    }

    /**
     * Get the value in the cache. If it does not found a value, it returns a default value.
     * This is needed as we can store null in the cache for a given key.
     */
    private function get(string $key)
    {
        if (array_key_exists($key, $this->data)) {
            $this->recordAccess($key);

            return $this->data[$key];
        }

        return $this->defaultValue;
    }

    /**
     * Put a value inside the cache.
     * If the cache reached its max size, it will drop the least recently used value.
     *
     * In this implementation, only the values that are not set are put in the cache.
     * It allows to avoid a check if the value exist in order to avoid a call to `recordAccess`.
     */
    private function put(string $key, $value): void
    {
        $this->data[$key] = $value;

        if (count($this->data) > $this->maximumSize) {
            reset($this->data);
            unset($this->data[key($this->data)]);
        }
    }

    /**
     * Put at the end of the list the element.
     * It uses PHP array behavior instead of a chained list as it's ~3x faster.
     */
    private function recordAccess(string $key): void
    {
        $value = $this->data[$key];
        unset($this->data[$key]);
        $this->data[$key] = $value;
    }
}
