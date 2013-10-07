<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * ORM reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    protected $query;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    private $executed = false;

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

            $result = $this->query->execute();
            $this->stepExecution->setReadCount(count($result));

            return empty($result) ? null : $result;
        }
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
