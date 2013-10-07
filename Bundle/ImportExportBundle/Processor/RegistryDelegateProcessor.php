<?php

namespace Oro\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

class RegistryDelegateProcessor implements ProcessorInterface, StepExecutionAwareInterface
{
    /**
     * @var ProcessorRegistry
     */
    protected $processorRegistry;

    /**
     * @var string
     */
    protected $delegateType;

    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @param ProcessorRegistry $processorRegistry
     * @param string $delegateType
     * @param ContextRegistry $contextRegistry
     */
    public function __construct(ProcessorRegistry $processorRegistry, $delegateType, ContextRegistry $contextRegistry)
    {
        $this->processorRegistry = $processorRegistry;
        $this->delegateType = $delegateType;
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $this->getDelegateProcessor()->process($item);
    }

    /**
     * @return ProcessorInterface
     * @throws InvalidConfigurationException
     * @throws LogicException
     */
    protected function getDelegateProcessor()
    {
        if (!$this->stepExecution) {
            throw new LogicException('Step execution entity must be injected to processor.');
        }
        $context = $this->contextRegistry->getByStepExecution($this->stepExecution);
        if (!$context->getOption('processorAlias')) {
            throw new InvalidConfigurationException(
                'Configuration of processor must contain "processorAlias" options.'
            );
        }

        $result = $this->processorRegistry->getProcessor(
            $this->delegateType,
            $context->getOption('processorAlias')
        );

        if ($result instanceof ContextAwareInterface) {
            $result->setImportExportContext($context);
        }

        if ($result instanceof StepExecutionAwareInterface) {
            $result->setStepExecution(
                $this->stepExecution
            );
        }

        return $result;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
