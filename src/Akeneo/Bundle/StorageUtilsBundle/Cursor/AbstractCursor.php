<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

/**
 * Class AbstractCursor to iterate product
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var int */
    protected $position;

    /** @var  QueryBuilder depending on the implementation */
    protected $queryBuilder;

    /**
     * @param $query
     */
    public function __construct($queryBuilder)
    {
        $this->queryBuilder = clone $queryBuilder;
    }

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
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->position < $this->count();
    }
}
