<?php

namespace Pim\Bundle\ImportExportBundle\Reader\ORM;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Pim\Bundle\ImportExportBundle\Exception\ORMReaderException;

/**
 * ORM cursor reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /**
     * @var AbstractQuery
     */
    protected $query;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    private $cursor;

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
        if (!$this->cursor) {
            $this->cursor = $this->getQuery()->iterate();
        }

        if ($data = $this->cursor->next()) {
            $this->stepExecution->incrementSummaryInfo('read');

            return $data;
        }
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
