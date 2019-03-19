<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

/**
 * Least Recently Used Cache
 *
 * A fixed sized cache that removes the element used last when it reaches its
 * size limit.
 */
class LRUCache
{

    /** @var int */
    private $maximumSize;

    /**
     * The head of the array contains the least recent used element
     *
     * @var array
     */
    private $data = [];

    /**
     * @param int $size
     * @throws \InvalidArgumentException
     */
    public function __construct(int $size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException();
        }

        $this->maximumSize = $size;
    }

    /**
     * Get the value cached with this key
     *
     * @param int|string $key     The key. Strings that are ints are cast to ints.
     * @param mixed      $default The value to be returned if key not found. (Optional)
     *
     * @return mixed
     */
    public function get(string $key): ?object
    {
        if (isset($this->data[$key])) {
            $this->recordAccess($key);
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Put something in the cache
     *
     * @param int|string $key   The key. Strings that are ints are cast to ints.
     * @param mixed      $value The value to cache
     */
    public function put(string $key, object $value)
    {
        if (isset($this->data[$key])) {
            $this->data[$key] = $value;
            $this->recordAccess($key);
        } else {
            $this->data[$key] = $value;
            if (count($this->data) > $this->maximumSize) {
                reset($this->data);
                unset($this->data[key($this->data)]);
            }
        }
    }

    /**
     * Moves the element from current position to end of array
     *
     * @param int|string $key The key
     */
    private function recordAccess(string $key)
    {
        $value = $this->data[$key];
        unset($this->data[$key]);
        $this->data[$key] = $value;
    }
}
