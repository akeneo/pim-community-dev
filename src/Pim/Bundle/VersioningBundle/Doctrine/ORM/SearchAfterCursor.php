<?php

declare(strict_types=1);

namespace Pim\Bundle\VersioningBundle\Doctrine\ORM;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\Versioning\Model\VersionInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterCursor implements CursorInterface
{
    /** @var int */
    private $position;

    /** @var int */
    private $lastVersionId;

    /** @var null|VersionInterface */
    private $currentVersion;

    /** @var null|\ArrayIterator */
    private $versionsPage;

    /** @var QueryBuilder */
    private $queryBuilder;

    /** @var int */
    private $pageSize;

    // @todo: see if we can remove $entityManager (need to modify CursorFactory or VersionRepository)
    public function __construct(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        int $pageSize
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->pageSize = $pageSize;
        $this->pageSize = $pageSize;
        $this->position = 0;
        $this->lastVersionId = 0;
        $this->versionsPage = null;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->currentVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->currentVersion = $this->getNextVersion();
        $this->position++;

        if (null !== $this->currentVersion) {
            $this->lastVersionId = $this->currentVersion->getId();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->valid() ? $this->position : null;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== $this->currentVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
        $this->lastVersionId = 0;
        $this->versionsPage = null;
        $this->currentVersion = $this->getNextVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        // Todo: modify and execute the query to count and fetch only the number of rows
    }

    private function getNextVersion(): ?VersionInterface
    {
        $nextVersion = null;

        if (null === $this->versionsPage || !$this->versionsPage->valid()) {
            $this->versionsPage = $this->getNextVersionsPage();
        }

        if ($this->versionsPage->valid()) {
            $nextVersion = $this->versionsPage->current();
            $this->versionsPage->next();
        }

        return $nextVersion;
    }

    private function getNextVersionsPage(): \Iterator
    {
        /**
         * TODO: find a better way to do that?
         *  The problem is that we need two queries: one to count the total number of rows, and one to paginate an fetch the versions
         */
        $queryBuilder = clone $this->queryBuilder;
        $rootAlias = current($queryBuilder->getRootAliases());

        $query = $queryBuilder
            ->andWhere(sprintf('%s.id > :last_id', $rootAlias))
            ->setParameter(':last_id', $this->lastVersionId)
            ->orderBy(sprintf('%s.id', $rootAlias))
            ->setMaxResults($this->pageSize)
            ->getQuery();

        // TODO: use $query->iterate ?
        $versionsPage = $query->getResult();
        $versionsPage = new \ArrayIterator($versionsPage);
        $versionsPage->rewind();

        return $versionsPage;
    }
}
