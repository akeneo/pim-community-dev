<?php

namespace Oro\Bundle\ImportExportBundle\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

class EntityReader implements ReaderInterface
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

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param StepExecution $stepExecution
     * @return object|null
     */
    public function read(StepExecution $stepExecution)
    {
        $iterator = $this->getSourceIterator();
        if (!$this->rewound) {
            $iterator->rewind();
            $this->rewound = true;
        }

        $result = null;
        if ($iterator->valid()) {
            $result = $iterator->current();
            $stepExecution->incrementReadCount();
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
     * @param StepExecution $stepExecution
     * @throws InvalidConfigurationException
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $configuration = $stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();

        if (isset($configuration['entityName'])) {
            $this->setSourceEntityName($configuration['entityName']);
        } elseif (isset($configuration['queryBuilder'])) {
            $this->setSourceQueryBuilder($configuration['queryBuilder']);
        } elseif (isset($configuration['query'])) {
            $this->setSourceQuery($configuration['query']);
        } else {
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
        $this->setSourceQueryBuilder($this->registry->getRepository($entityName)->createQueryBuilder('o'));
    }

    /**
     * @param \Iterator $sourceIterator
     */
    public function setSourceIterator(\Iterator $sourceIterator)
    {
        $this->sourceIterator = $sourceIterator;
    }
}
