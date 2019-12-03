<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * @author    Quentin Favrie <quentin.favrie@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchVersionAfterCursor implements CursorInterface
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var \Generator
     */
    private $iterator;

    /**
     * @var int|null
     */
    private $count = null;

    public function __construct(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        int $pageSize
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->entityManager = $entityManager;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritDoc}
     *
     * @return VersionInterface
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->iterator = $this->iterator();
        $this->iterator->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->countQueryResults($this->queryBuilder);
    }

    private function countQueryResults(QueryBuilder $queryBuilder): int
    {
        $qb = clone $queryBuilder;
        $qb->select('COUNT(1)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    private function iterator(): \Generator
    {
        $lastId = null;

        while (true) {
            $qb = clone $this->queryBuilder;
            $rootAlias = current($qb->getRootAliases());

            if (null !== $lastId) {
                $qb->andWhere(sprintf('%s.id > :last', $rootAlias))
                    ->setParameter(':last', $lastId);
            }

            $qb->orderBy(sprintf('%s.id', $rootAlias))
                ->setMaxResults($this->pageSize);

            $rows = $qb->getQuery()->getResult();

            if (count($rows) === 0) {
                return null;
            }

            foreach ($rows as $entity) {
                yield $entity;
                $lastId = $entity->getId();
            }
        }
    }
}
