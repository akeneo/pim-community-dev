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
    /** @type int */
    protected $position;

    /** @var  Query */
    protected $queryBuilder;

    /**
     * @param $query
     */
    public function __construct($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
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
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->position < $this->count();
    }
}
