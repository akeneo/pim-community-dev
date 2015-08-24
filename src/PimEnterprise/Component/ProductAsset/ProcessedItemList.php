<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use Doctrine\Common\Util\ClassUtils;

/**
 * List items that have been processed.
 *
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class ProcessedItemList implements \Iterator, \Countable, \ArrayAccess
{
    /** @var int */
    protected $position = 0;

    /** @var ProcessedItem[] */
    protected $items = [];

    /** @var array */
    protected $statesCounter = [];

    /**
     * @param mixed      $item
     * @param string     $state
     * @param string     $reason
     * @param \Exception $e
     */
    public function addItem($item, $state, $reason = null, \Exception $e = null)
    {
        $this->items[] = new ProcessedItem($item, $state, $reason, $e);
        if (isset($this->statesCounter[$state])) {
            $this->statesCounter[$state]++;
        } else {
            $this->statesCounter[$state] = 1;
        }
    }

    /**
     * @return ProcessedItem
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
        if (!$value instanceof ProcessedItem) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\ProcessedItem", "%s" provided.',
                    ClassUtils::getClass($value)
                )
            );
        }

        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }

        if (isset($this->statesCounter[$value->getState()])) {
            $this->statesCounter[$value->getState()]++;
        } else {
            $this->statesCounter[$value->getState()] = 1;
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
        $item = $this->items[$offset];
        $this->statesCounter[$item->getState()]--;

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

    /**
     * @param string $state
     *
     * @return bool
     */
    public function hasItemInState($state)
    {
        return isset($this->statesCounter[$state]) && ($this->statesCounter[$state] > 0);
    }

    /**
     * @param $state
     *
     * @return ProcessedItem[]
     */
    public function getItemsInState($state)
    {
        return array_filter($this->items, function (ProcessedItem $item) use ($state) {
            return $state === $item->getState();
        });
    }
}
