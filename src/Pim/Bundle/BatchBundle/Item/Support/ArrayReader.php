<?php

namespace Pim\Bundle\BatchBundle\Item\Support;

use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Simple reader that provides data from an array
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function read(StepExecution $stepExecution)
    {
        $item = null;

        if ($this->readIndex < count($this->items)) {
            $item = $this->items[$this->readIndex];
            $this->readIndex++;
        }

        return $item;
    }
}
