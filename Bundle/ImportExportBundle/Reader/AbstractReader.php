<?php

namespace Oro\Bundle\ImportExportBundle\Reader;

use Oro\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

abstract class AbstractReader implements ReaderInterface
{
    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @param ContextRegistry $contextRegistry
     */
    public function __construct(ContextRegistry $contextRegistry)
    {
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        $this->initializeFromContext($this->getContext());
    }

    /**
     * @return StepExecution
     * @throws LogicException
     */
    protected function getStepExecution()
    {
        if (!$this->stepExecution) {
            throw new LogicException('Step execution must be set');
        }

        return $this->stepExecution;
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->contextRegistry->getByStepExecution($this->getStepExecution());
    }

    /**
     * Should be overridden in descendant classes
     *
     * @param ContextInterface $context
     */
    protected function initializeFromContext(ContextInterface $context)
    {
    }
}
