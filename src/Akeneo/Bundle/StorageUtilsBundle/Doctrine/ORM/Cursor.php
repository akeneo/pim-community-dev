<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM;

use ArrayIterator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\ModelRepositoryInterface;
use LogicException;

/**
 * Class Cursor to iterate entities from QueryBuilder
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class Cursor extends AbstractCursor
{
    /** @var int */
    protected $position = 0;

    /** @var array */
    protected $entitiesIds = null;

    /** @var int */
    protected $count = null;

    /** @var \ArrayIterator */
    protected $entitiesPage = null;

    /** @var EntityManager  */
    protected $entityManager;

    /** @var ModelRepositoryInterface */
    protected $repository = null;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $currentPage;

    /** @var Object */
    protected $entity;

    /**
     * @param QueryBuilder  $queryBuilder
     * @param EntityManager $entityManager
     * @param int           $pageSize
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        $pageSize
    ) {
        $this->queryBuilder = clone $queryBuilder;
        $this->entityManager = $entityManager;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        parent::next();
        $this->entity = $this->getNextEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->entity;
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
        $this->entitiesPage = null;
        $this->entity = $this->getNextEntity();
    }

    /**
     * Get ids of entities from the QueryBuilder
     *
     * @return array
     */
    protected function getEntitiesIds()
    {
        if ($this->entitiesIds === null) {
            $rootAlias = current($this->queryBuilder->getRootAliases());
            $rootIdExpr = sprintf('%s.id', $rootAlias);

            $from = current($this->queryBuilder->getDQLPart('from'));

            $this->queryBuilder
                ->select($rootIdExpr)
                ->resetDQLPart('from')
                ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
                ->groupBy($rootIdExpr);

            $results = $this->queryBuilder->getQuery()->getArrayResult();
            $this->entitiesIds = array_keys($results);
        }
        return $this->entitiesIds;
    }

    /**
     * @return int
     */
    protected function getOffSet()
    {
        return $this->pageSize * $this->currentPage;
    }

    /**
     * @return ModelRepositoryInterface
     * @throws LogicException
     */
    protected function getRepository()
    {
        if ($this->repository === null) {
            $entityClass = current($this->queryBuilder->getDQLPart('from'))->getFrom();
            $this->repository = $this->entityManager->getRepository($entityClass);
            if (!($this->repository instanceof ModelRepositoryInterface)) {
                throw new LogicException(sprintf('%s repository must implement ModelRepositoryInterface', $entityClass));
            }
        }

        return $this->repository;
    }

    /**
     *
     */
    protected function getNextEntity()
    {
        $entity = false;

        if ($this->entitiesPage === null || !$this->entitiesPage->valid()) {
            $this->entitiesPage = $this->getNextEntitiesPage();
        }
        if ($this->entitiesPage !== null) {
            $entity = $this->entitiesPage->current();
            $this->entitiesPage->next();
        }

        return $entity;
    }

    /**
     * Get next products batch from DB
     *
     * @return \ArrayIterator
     */
    protected function getNextEntitiesPage()
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
