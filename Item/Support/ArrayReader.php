<?php

namespace Akeneo\Bundle\BatchBundle\Item\Support;

use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Simple reader that provides data from an array
 *
 */
class ArrayReader implements ItemReaderInterface
{
    protected $readIndex = 0;
    protected $items;

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $item = null;

        if ($this->readIndex < count($this->items)) {
            $item = $this->items[$this->readIndex];
            $this->readIndex++;
        }

        return $item;
    }
}
