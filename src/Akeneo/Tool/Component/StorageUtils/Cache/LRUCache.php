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
        $this->maximumSize = $size;
    }

    /**
     * This methods gets what is stored on cache, complete with values to fetch and save every key with values
     * This methods is like that for performances reason
     */
    public function getOrSave(array $keys, callable $fetchNonFoundKeys, callable $transformDataToKeyValues, $valueForNonExistentData): array
    {
        $fromCacheIndexedByKey = [];
        $keysToFetch = [];
        $defaultValue = sha1('DEFAULT_CACHED_VALUE');

        foreach ($keys as $key) {
            $value = $this->getOrElse($key, $defaultValue);
            if ($defaultValue === $value) {
                $keysToFetch[] = $key;
            } else {
                $fromCacheIndexedByKey[$key] = $value;
            }
        }

        $fetched = $fetchNonFoundKeys($keysToFetch);
        $keyValuesDataFetched = $transformDataToKeyValues($fetched);

        foreach ($keyValuesDataFetched as $key => $value) {
            $this->put((string) $key, $value);
        }

        $nonExistentKeys = array_diff($keys, array_keys($keyValuesDataFetched), array_keys($fromCacheIndexedByKey));

        foreach ($nonExistentKeys as $nonExistentKey) {
            $this->put((string) $nonExistentKey, $valueForNonExistentData);
        }

        return array_values(array_merge($fetched, $fromCacheIndexedByKey));
    }

    /**
     * This methods is inspired by monadic option (in other languages) to get a default value and not let the language crashes
     */
    public function getOrElse(string $key, $default)
    {
        if (isset($this->data[$key])) {
            $this->recordAccess($key);
            if ($this->data[$key] === $this->nullData) {
                return null;
            }

            return $this->data[$key];
        }

        return $default;
    }

    public function put(string $key, $value): void
    {
        if ($value === null) {
            $value = $this->nullData;
        }

        if (isset($this->data[$key])) {
            $this->data[$key] = $value;
            $this->recordAccess($key);
            return;
        }

        $this->data[$key] = $value;
        if (count($this->data) > $this->maximumSize) {
            reset($this->data);
            unset($this->data[key($this->data)]);
        }
    }

    private function recordAccess(string $key): void
    {
        $value = $this->data[$key];
        unset($this->data[$key]);
        $this->data[$key] = $value;
    }
}
