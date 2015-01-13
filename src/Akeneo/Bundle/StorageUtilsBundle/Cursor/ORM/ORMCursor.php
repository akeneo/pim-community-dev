<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM;

use ArrayIterator;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\EntityRepositoryInterface;

/**
 * Class ORMCursor to iterate entities from QueryBuilder
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ORMCursor extends AbstractCursor
{
    /** @type int */
    protected $position = 0;

    /** @type array */
    protected $entitiesIds = null;

    /** @type int */
    protected $count = null;

    /** @type \ArrayIterator */
    protected $entitiesPage;

    /** @type EntityManager  */
    protected $entityManager;

    /** @type EntityRepositoryInterface */
    protected $repository;

    /** @type int */
    protected $pageSize;

    /** @type int */
    protected $currentPage;

    /**
     * @param QueryBuilder              $queryBuilder
     * @param EntityManager             $entityManager
     * @param EntityRepositoryInterface $repository
     * @param int                       $pageSize
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        EntityRepositoryInterface $repository,
        $pageSize
    ) {
        parent::__construct($queryBuilder);
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $entity = null;

        if (!$this->entitiesPage->valid()) {
            $this->entitiesPage = $this->getNextEntities();
        }

        if (null !== $this->entitiesPage) {
            $entity = $this->entitiesPage->current();
            $this->entitiesPage->next();
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = count($this->getEntitiesIds());
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }


    /**
     * @return ArrayIterator
     */
    protected function getEntitiesIds()
    {
        if ($this->entitiesIds === null) {
            $this->entitiesIds = $this->getIds();
        }
        return $this->entitiesIds;
    }

    /**
     * @return Query
     */
    protected function getQuery()
    {
        return $this->queryBuilder->getQuery();
    }

    /**
     * Get ids of entities from the QueryBuilder
     *
     * @return array
     */
    protected function getIds()
    {
        $rootAlias = current($this->getQuery()->getRootAliases());
        $rootIdExpr = sprintf('%s.id', $rootAlias);

        $from = current($this->getQuery()->getDQLPart('from'));

        $this->getQuery()
            ->select($rootIdExpr)
            ->resetDQLPart('from')
            ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
            ->groupBy($rootIdExpr);

        $results = $this->getQuery()->getQuery()->getArrayResult();

        return array_keys($results);
    }

    /**
     * @return int
     */
    protected function getOffSet()
    {
        return $this->pageSize * $this->currentPage;
    }

    /**
     * Get next products batch from DB
     *
     * @return \ArrayIterator
     */
    protected function getNextEntities()
    {
        $this->entityManager->clear();
        $entities = null;

        $currentIds = array_slice($this->getEntitiesIds(), $this->getOffSet(), $this->pageSize);

        if (!empty($currentIds)) {
            $items = $this->repository->findByIds($currentIds);
            $entities = new \ArrayIterator($items);
            $this->offset += $this->limit;
        }

        return $entities;
    }
}
