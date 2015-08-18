<?php

namespace PimEnterprise\Component\ProductAsset;

/**
 * TODO
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class ProcessedItemList implements \Iterator, \Countable, \ArrayAccess
{
    /** @var int */
    protected $position = 0;

    /** @var ProcessedItem[] */
    protected $items = [];

    /**
     * @param mixed  $item
     * @param string $state
     * @param null   $reason
     */
    public function addItem($item, $state, $reason = null)
    {
        $this->items[] = new ProcessedItem($item, $state, $reason);
    }

    /**
     * @return ProcessedItem
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return null|ProcessedItem
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }
}
