<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\AbstractCursor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
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
    /** @var QueryBuilder */
    protected $queryBuilder;

    /** @var int */
    protected $position = 0;

    /** @var array */
    protected $entitiesIds;

    /** @var int */
    protected $count;

    /** @var \ArrayIterator */
    protected $entitiesPage;

    /** @var EntityManager */
    protected $entityManager;

    /** @var CursorableRepositoryInterface */
    protected $repository;

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
        $this->queryBuilder = $queryBuilder;
        $this->entityManager = $entityManager;
        $this->pageSize = $pageSize;
        $this->rewind();
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
        if (null === $this->count) {
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
        if (null === $this->entitiesIds) {
            $rootAlias = current($this->queryBuilder->getRootAliases());
            $rootIdExpr = sprintf('%s.id', $rootAlias);

            $from = current($this->queryBuilder->getDQLPart('from'));

            $this->queryBuilder
                ->select($rootIdExpr)
                ->resetDQLPart('from')
                ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
                ->distinct(true);

            $query = $this->queryBuilder->getQuery();
            $query->useQueryCache(false);

            $results = $query->getArrayResult();
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
     * @throws LogicException
     *
     * @return CursorableRepositoryInterface
     */
    protected function getRepository()
    {
        if (null === $this->repository) {
            $entityClass = current($this->queryBuilder->getDQLPart('from'))->getFrom();
            $this->repository = $this->entityManager->getRepository($entityClass);

            if (!$this->repository instanceof CursorableRepositoryInterface) {
                throw new LogicException(
                    sprintf(
                        '"%s" repository must implement ' .
                        '"Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface"',
                        $entityClass
                    )
                );
            }
        }

        return $this->repository;
    }

    /**
     * Get next entity
     *
     * @return bool|mixed
     */
    protected function getNextEntity()
    {
        $entity = false;

        if (null === $this->entitiesPage || !$this->entitiesPage->valid()) {
            $this->entitiesPage = $this->getNextEntitiesPage();
        }
        if ($this->entitiesPage !== null) {
            $entity = $this->entitiesPage->current();
            $this->entitiesPage->next();
        }

        return $entity;
    }

    /**
     * Get next entities batch from DB
     *
     * @return \ArrayIterator
     */
    protected function getNextEntitiesPage()
    {
        $entities = null;
        $currentIds = array_slice($this->getEntitiesIds(), $this->getOffSet(), $this->pageSize);

        if (!empty($currentIds)) {
            $items = $this->getRepository()->findByIds($currentIds);
            $this->currentPage++;
            $orderedResult = array_fill_keys($currentIds, null);
            foreach ($items as $entity) {
                $entityId = null;
                if (is_object($entity)) {
                    $entityId = $entity->getId();
                } elseif (is_array($entity) && array_key_exists('id', $entity)) {
                    $entityId = $entity['id'];
                }
                if ($entityId) {
                    $orderedResult[$entityId] = $entity;
                }
            }
            $entities = new \ArrayIterator($orderedResult);
        }

        return $entities;
    }
}
