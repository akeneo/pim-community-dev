<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor;

use Doctrine\ODM\MongoDB\Cursor as CursorMongoDB;
use Doctrine\ODM\MongoDB\Query\Builder;
use Akeneo\Component\StorageUtils\Cursor\AbstractCursor;

/**
 * Class Cursor to iterate entities from Builder
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Cursor extends AbstractCursor
{
    /** @var Builder */
    protected $queryBuilder;

    /** @var CursorMongoDB */
    protected $cursor;

    /** @var int */
    protected $count;

    /** @var int */
    protected $batchSize;

    /** @var mixed */
    protected $currentDocument;

    /**
     * @param Builder $queryBuilder
     * @param int     $batchSize    : set MongoCursor::batchSize — Limits the number of elements returned in one batch.
     */
    public function __construct(Builder $queryBuilder, $batchSize = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->count) {
            $this->count = $this->getCursor()->count();
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->getCursor()->reset();
        $this->next();
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        parent::next();
        $this->currentDocument = $this->getCursor()->getNext();
        if (null === $this->currentDocument) {
            $this->currentDocument = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->currentDocument;
    }

    /**
     * Give a cursor and create it if necessary
     *
     * @return CursorMongoDB
     */
    protected function getCursor()
    {
        if (null === $this->cursor) {
            $this->cursor = $this->queryBuilder->getQuery()->execute();
            if (null !== $this->batchSize) {
                $this->cursor->batchSize($this->batchSize);
            }
            // MongoDB Cursor are not positioned on first element (whereas ArrayIterator is)
            // as long as getNext() hasn't be called
            $this->cursor->getNext();
        }

        return $this->cursor;
    }
}
