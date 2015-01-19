<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Cursor as CursorMongoDB;
use Doctrine\ODM\MongoDB\Query\Builder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;

/**
 * Class Cursor to iterate entities from Builder
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Cursor extends AbstractCursor
{
    /** @var CursorMongoDB */
    protected $cursor = null;

    /** @var int */
    protected $count = null;

    /** @var int */
    protected $batchSize;

    /** @var mixed */
    protected $currentDocument;

    /**
     * @param Builder $queryBuilder
     * @param int     $batchSize : set MongoCursor::batchSize — Limits the number of elements returned in one batch.
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
        if ($this->count === null) {
            $this->count = $this->getCursor()->count();
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
        $this->getCursor()->reset();
        $this->currentDocument = $this->getCursor()->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        parent::next();
        $this->currentDocument = $this->getCursor()->getNext();
        if ($this->currentDocument===null){
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
        if ($this->cursor === null) {
            $this->cursor = $this->queryBuilder->getQuery()->execute();
            if ($this->batchSize !== null) {
                $this->cursor->batchSize($this->batchSize);
            }
            // MongoDB Cursor are not positioned on first element (whereas ArrayIterator is)
            // as long as getNext() hasn't be called
            $this->cursor->getNext();
        }

        return $this->cursor;
    }
}
