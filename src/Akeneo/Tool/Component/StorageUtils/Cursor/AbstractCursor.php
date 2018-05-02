<?php

namespace Akeneo\Tool\Component\StorageUtils\Cursor;

/**
 * Class AbstractCursor to iterate items
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var int */
    protected $position;

    /**
     * {@inheritdoc}
     */
    abstract public function count();

    /**
     * {@inheritdoc}
     */
    abstract public function rewind();

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if ($this->valid()) {
            return $this->position;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->position < $this->count();
    }
}
