<?php

namespace PimEnterprise\Component\ProductAsset;

class ProcessedItemList implements \Iterator, \Countable
{
    /** @var int */
    protected $position = 0;

    /** @var ProcessedItem[] */
    protected $items = [];

    public function addItem($item, $state, $reason = null)
    {
        $this->items[] = new ProcessedItem($item, $state, $reason);
    }

    public function current()
    {
        return $this->items[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function count()
    {
        return count($this->items);
    }
}
