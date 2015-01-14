<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor\MongoDBODM;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\Query\Builder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;
use Doctrine\ORM\EntityManager;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\EntityRepositoryInterface;

/**
 * Class MongoDBODMCursor to iterate entities from Builder
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class MongoDBODMCursor extends AbstractCursor
{
    /** @type Cursor */
    protected $cursor = null;

    /** @type int */
    protected $count = null;

    /** @type EntityManager */
    protected $entityManager;

    /** @type EntityRepositoryInterface */
    protected $repository;

    /** @type int */
    protected $batchSize;

    /**
     * @param Builder $queryBuilder
     * @param int     $batchSize    : set MongoCursor::batchSize — Limits the number of elements returned in one batch.
     * @internal param $query
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
        $this->getCursor()->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $entity = $this->getCursor()->current();

        if ($entity) {
            $this->getCursor()->getNext();
        }

        return $entity;
    }

    /**
     * Give a cursor and create it if necessary
     *
     * @return Cursor
     */
    protected function getCursor()
    {
        if ($this->cursor === null) {
            $this->cursor = $this->queryBuilder->getQuery()->execute();
            if ($this->batchSize !== null) {
                $this->cursor->setBatchSize($this->batchSize);
            }
            // MongoDB Cursor are not positioned on first element (whereas ArrayIterator is)
            // as long as getNext() hasn't be called
            $this->cursor->getNext();
        }

        return $this->cursor;
    }
}
