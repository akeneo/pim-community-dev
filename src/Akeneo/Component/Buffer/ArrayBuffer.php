<?php

namespace Akeneo\Component\Buffer;

/**
 * Basic implementation of BufferInteraface using a simple FIFO array.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ArrayBuffer implements BufferInterface
{
    /** @var array */
    private $items = [];

    /**
     * {@inheritdoc}
     */
    public function write($item)
    {
        array_push($this->items, $item);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->items);
    }
}
