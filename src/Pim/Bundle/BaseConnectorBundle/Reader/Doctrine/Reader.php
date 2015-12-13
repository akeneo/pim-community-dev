<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\BaseConnectorBundle\Exception\ORMReaderException;

/**
 * ORM reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Reader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var AbstractQuery */
    protected $query;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool */
    private $executed = false;

    /** @var array */
    protected $results = [];

    /**
     * Set query used by the reader
     *
     * @param mixed $query
     *
     * @throws InvalidArgumentException
     */
    public function setQuery($query)
    {
        if (!is_a($query, 'Doctrine\ORM\AbstractQuery', true) && !is_a($query, 'Doctrine\MongoDB\Query\Query', true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$query must be either a Doctrine\ORM\AbstractQuery or ' .
                    'a Doctrine\ODM\MongoDB\Query\Query instance, got "%s"',
                    is_object($query) ? get_class($query) : $query
                )
            );
        }
        $this->query = $query;
    }

    /**
     * Get query to execute
     *
     * @throws ORMReaderException
     *
     * @return Doctrine\ORM\AbstractQuery|Doctrine\MongoDB\Query\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->executed) {
            $this->executed = true;

            $this->results = $this->getQuery()->execute();
            if (!$this->results instanceof \Iterator) {
                $this->results = new \ArrayIterator($this->results);
            }
            if (is_a($this->results, 'Doctrine\MongoDB\Cursor', true)) {
                // MongoDB Cursor are not positionned on first element (whereas ArrayIterator is)
                // as long as getNext() hasn't be called
                $this->results->getNext();
            }
        }

        if ($result = $this->results->current()) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->executed = false;
    }
}
