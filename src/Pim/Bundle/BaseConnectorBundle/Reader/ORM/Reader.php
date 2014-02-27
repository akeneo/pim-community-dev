<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Doctrine\ORM\AbstractQuery;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

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

    /** @var boolean */
    private $executed = false;

    /** @var array */
    protected $results = array();

    /**
     * Set query used by the reader
     * @param AbstractQuery $query
     */
    public function setQuery(AbstractQuery $query)
    {
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->executed) {
            $this->executed = true;

            $this->results = $this->getQuery()->execute();
        }

        if ($result = array_shift($this->results)) {
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $result;
    }

    /**
     * Get query to execute
     *
     * @return \Doctrine\ORM\AbstractQuery
     *
     * @throws ORMReaderException
     */
    protected function getQuery()
    {
        if (!$this->query) {
            throw new ORMReaderException('Need a query to read database');
        }

        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
