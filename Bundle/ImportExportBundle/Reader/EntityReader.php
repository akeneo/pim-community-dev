<?php

namespace Oro\Bundle\ImportExportBundle\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class EntityReader extends AbstractReader
{
    /**
     * @var \Iterator
     */
    protected $sourceIterator;

    /**
     * @var bool
     */
    protected $rewound = false;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ContextRegistry $contextRegistry
     * @param ManagerRegistry $registry
     */
    public function __construct(ContextRegistry $contextRegistry, ManagerRegistry $registry)
    {
        parent::__construct($contextRegistry);

        $this->registry = $registry;
    }

    /**
     * @return object|null
     */
    public function read()
    {
        $iterator = $this->getSourceIterator();
        if (!$this->rewound) {
            $iterator->rewind();
            $this->rewound = true;
        }

        $result = null;
        if ($iterator->valid()) {
            $result = $iterator->current();
            $context = $this->getContext();
            $context->incrementReadOffset();
            $context->incrementReadCount();
            $iterator->next();
        }

        return $result;
    }

    /**
     * @return \Iterator
     * @throws LogicException
     */
    protected function getSourceIterator()
    {
        if (null === $this->sourceIterator) {
            throw new LogicException('Reader must be configured with source');
        }
        return $this->sourceIterator;
    }

    /**
     * @param ContextInterface $context
     * @throws InvalidConfigurationException
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->hasOption('entityName')) {
            $this->setSourceEntityName($context->getOption('entityName'));
        } elseif ($context->hasOption('queryBuilder')) {
            $this->setSourceQueryBuilder($context->getOption('queryBuilder'));
        } elseif ($context->hasOption('query')) {
            $this->setSourceQuery($context->getOption('query'));
        } elseif (!$this->sourceIterator) {
            throw new InvalidConfigurationException(
                'Configuration of entity reader must contain either "entityName", "queryBuilder" or "query".'
            );
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setSourceQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->sourceIterator = new BufferedQueryResultIterator($queryBuilder);
    }

    /**
     * @param Query $query
     */
    public function setSourceQuery(Query $query)
    {
        $this->sourceIterator = new BufferedQueryResultIterator($query);
    }

    /**
     * @param string $entityName
     */
    public function setSourceEntityName($entityName)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->registry->getRepository($entityName)->createQueryBuilder('o');

        $metadata = $qb->getEntityManager()->getClassMetadata($entityName);
        foreach ($metadata->getAssociationMappings() as $assocMapping) {
            $alias = '_' . $assocMapping['fieldName'];
            $qb->addSelect($alias);
            $qb->leftJoin('o.' . $assocMapping['fieldName'], $alias);
        }

        $this->setSourceQueryBuilder($qb);
    }

    /**
     * @param \Iterator $sourceIterator
     */
    public function setSourceIterator(\Iterator $sourceIterator)
    {
        $this->sourceIterator = $sourceIterator;
    }
}
