<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * A reader based on a method of a repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RepositoryReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var boolean */
    protected $executed = false;

    /** @var \ArrayIterator */
    protected $results;

    /** @var ReferableEntityRepositoryInterface */
    protected $repository;

    /** @var string */
    protected $method;

    /**
     * @param ReferableEntityRepositoryInterface $repository The repository
     * @param string                             $method     The method that will be called
     */
    public function __construct(ReferableEntityRepositoryInterface $repository, $method)
    {
        $this->repository = $repository;
        $this->method     = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->executed) {
            $this->executed = true;
            $method = $this->method;
            $this->results = $this->repository->$method();
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
