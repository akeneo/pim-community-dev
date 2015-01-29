<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\Repository;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;

/**
 * The group reader
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var boolean */
    protected $executed = false;

    /** @var \ArrayIterator */
    protected $results;

    /** @var GroupRepository */
    protected $repository;

    /**
     * @param GroupRepository $repository
     */
    public function __construct(GroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->executed) {
            $this->executed = true;
            $this->results = $this->repository->getAllGroupsExceptVariant();
            if (!$this->results instanceof \Iterator) {
                $this->results = new \ArrayIterator($this->results);
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
