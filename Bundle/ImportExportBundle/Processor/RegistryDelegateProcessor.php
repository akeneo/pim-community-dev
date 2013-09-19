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
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @param ProcessorRegistry $processorRegistry
     * @param ContextRegistry $contextRegistry
     */
    public function __construct(ProcessorRegistry $processorRegistry, ContextRegistry $contextRegistry)
    {
        $this->processorRegistry = $processorRegistry;
        $this->contextRegistry = $contextRegistry;
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
        if (!$this->stepExecution) {
            throw new LogicException('Step execution entity must be injected to processor.');
        }
        $configuration = $this->stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();
        if (empty($configuration['entityName']) || empty($configuration['processorAlias'])) {
            throw new InvalidConfigurationException(
                'Configuration of processor must contain "entityName" and "processorAlias" options.'
            );
        }

        $result = $this->processorRegistry->getProcessor(
            $configuration['entityName'],
            $configuration['processorAlias']
        );

        if ($result instanceof ContextAwareInterface) {
            $result->setImportExportContext(
                $this->contextRegistry->getByStepExecution($this->stepExecution)
            );
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
