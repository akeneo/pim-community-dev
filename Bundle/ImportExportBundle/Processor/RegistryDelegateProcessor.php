<?php

namespace Oro\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

class RegistryDelegateProcessor implements ProcessorInterface, StepExecutionAwareInterface
{
    /**
     * @var ProcessorInterface
     */
    protected $delegateProcessor;

    /**
     * @var ProcessorRegistry
     */
    protected $registry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @param ProcessorRegistry $registry
     */
    public function __construct(ProcessorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $this->getDelegateProcessor()->process($item);
    }

    /**
     * @return ProcessorInterface
     * @throws InvalidConfigurationException
     * @throws LogicException
     */
    protected function getDelegateProcessor()
    {
        if (null === $this->delegateProcessor) {
            if (!$this->stepExecution) {
                throw new LogicException('Step execution entity must be injected to processor.');
            }
            $configuration = $this->stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();
            if (isset($configuration['entityName']) && isset($configuration['processorAlias'])) {
                $this->delegateProcessor = $this->registry->getProcessor(
                    $configuration['entityName'],
                    $configuration['processorAlias']
                );
                if ($this->delegateProcessor instanceof StepExecutionAwareInterface) {
                    $this->delegateProcessor->setStepExecution($this->stepExecution);
                }
            } else {
                throw new InvalidConfigurationException(
                    'Configuration of processor must contain "entityName" and "processorAlias" options.'
                );
            }
        }

        return $this->delegateProcessor;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
