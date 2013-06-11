<?php

namespace Oro\Bundle\ImportExportBundle\Reader;

use Ddeboer\DataImport\Reader\ReaderInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

class FlexibleEntityReader implements ReaderInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $flexibleRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var int
     */
    protected $batchSize = 1000;

    /**
     * @var \Iterator
     */
    protected $pageIterator;

    /**
     * @var int
     */
    protected $pageIndex;

    /**
     * @var int
     */
    protected $lastPage;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @param FlexibleManagerRegistry $flexibleManagerRegistry
     * @param string $entityName
     */
    public function __construct(FlexibleManagerRegistry $flexibleManagerRegistry, $entityName)
    {
        $this->flexibleManager = $flexibleManagerRegistry->getManager($entityName);
        $this->flexibleRepository = $this->flexibleManager->getFlexibleRepository();
        $this->objectManager = $this->flexibleManager->getStorageManager();
        $this->entityName = $entityName;
    }

    /**
     * @param int $batchSize
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->pageIterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->pageIterator->next();
        if (!$this->pageIterator->valid() && $this->hasNextPage()) {
            $this->pageIndex += 1;
            $this->pageIterator = $this->getPageIterator($this->pageIndex);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if ($this->pageIterator->valid() || $this->hasNextPage()) {
            return $this->pageIterator->key() + $this->pageIndex * $this->batchSize;
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->pageIterator->valid() || $this->hasNextPage();
    }

    /**
     * @return bool
     */
    private function hasNextPage()
    {
        return $this->pageIndex != $this->lastPage;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (null === $this->totalCount) {
            $this->totalCount = $this->getTotalCount();
            $this->lastPage = ceil($this->totalCount / $this->batchSize) - 1;
        }
        if (0 == $this->pageIndex && $this->pageIterator) {
            $this->pageIterator->rewind();
        } else {
            $this->pageIterator = $this->getPageIterator(0);
        }
    }

    /**
     * @param int $page
     * @return Paginator
     */
    protected function getPageIterator($page)
    {
        /** @var FlexibleQueryBuilder $queryBuilder */
        $queryBuilder = $this->flexibleRepository->createFlexibleQueryBuilder('o');
        $queryBuilder->setFirstResult($page * $this->batchSize)->setMaxResults($this->batchSize);
        $paginator = new Paginator($queryBuilder->getQuery(), $fetchJoinCollection = true);
        return $paginator->getIterator();
    }

    /**
     * @return int
     */
    protected function getTotalCount()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->flexibleRepository->createQueryBuilder('o');
        return $queryBuilder->select('count(o.id)')->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        if (null === $this->fields) {
            $doctrineFieldNames = $this->objectManager->getClassMetadata($this->entityName)
                ->getFieldNames();

            $flexibleAttributesCodes = array_keys($this->flexibleRepository->getCodeToAttributes(array()));
            $this->fields = array_merge($doctrineFieldNames, $flexibleAttributesCodes);
        }
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->totalCount) {
            $this->totalCount = $this->getTotalCount();
        }
        return $this->totalCount;
    }
}
