<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM;

use ArrayIterator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\EntityRepositoryInterface;
use Exception;

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
    protected $entitiesPage = null;

    /** @type EntityManager  */
    protected $entityManager;

    /** @type EntityRepositoryInterface */
    protected $repository = null;

    /** @type int */
    protected $pageSize;

    /** @type int */
    protected $currentPage;

    /**
     * @param QueryBuilder              $queryBuilder
     * @param EntityManager             $entityManager
     * @param int                       $pageSize
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        $pageSize
    ) {
        parent::__construct($queryBuilder);
        $this->entityManager = $entityManager;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $entity = null;
        if ($this->entitiesPage === null || !$this->entitiesPage->valid()) {
            $this->entitiesPage = $this->getNextEntities();
        }
        if ($this->entitiesPage !== null) {
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
        $this->currentPage = 0;
    }

    /**
     * @return array
     */
    protected function getEntitiesIds()
    {
        if ($this->entitiesIds === null) {
            $this->entitiesIds = $this->getIds();
        }
        return $this->entitiesIds;
    }

    /**
     * Get ids of entities from the QueryBuilder
     *
     * @return array
     */
    protected function getIds()
    {
        $rootAlias = current($this->queryBuilder->getRootAliases());
        $rootIdExpr = sprintf('%s.id', $rootAlias);

        $from = current($this->queryBuilder->getDQLPart('from'));

        $this->queryBuilder
            ->select($rootIdExpr)
            ->resetDQLPart('from')
            ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
            ->groupBy($rootIdExpr);

        $results = $this->queryBuilder->getQuery()->getArrayResult();

        return array_keys($results);
    }

    /**
     * @return int
     */
    protected function getOffSet()
    {
        return $this->pageSize * $this->currentPage;
    }

    protected function getRepository()
    {
        if ($this->repository === null) {
            $entityClass = current($this->queryBuilder->getDQLPart('from'))->getFrom();
            $this->repository = $this->entityManager->getRepository($entityClass);
            if(!($this->repository instanceof EntityRepositoryInterface)) {
                throw new Exception(sprintf('%s repository must implement EntityRepositoryInterface',$entityClass));
            }
        }
        return $this->repository;
    }


    /**
     * Get next products batch from DB
     *
     * @return \ArrayIterator
     */
    protected function getNextEntities()
    {
        $entities = null;
        $currentIds = array_slice($this->getEntitiesIds(), $this->getOffSet(), $this->pageSize);

        if (!empty($currentIds)) {
            $items = $this->getRepository()->findByIds($currentIds);
            $entities = new \ArrayIterator($items);
            $this->currentPage++;
        }

        return $entities;
    }
}
