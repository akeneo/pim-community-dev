<?php

namespace Oro\Bundle\ImportExportBundle\Context;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Exception\ErrorException;

class StepExecutionProxyContext implements ContextInterface
{
    /**
     * @var StepExecution
     */
    protected $stepExecution;

    public function __construct(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function addError($message, $severity = null)
    {
        $severity = (null === $severity) ? ErrorException::CRITICAL : $severity;
        $exception = new ErrorException($message, 0, $severity);
        $this->stepExecution->addFailureException($exception);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        $errors = array();

        foreach ($this->stepExecution->getFailureExceptions() as $exceptionData) {
            if (!empty($exceptionData['message'])) {
                $errors[] = $exceptionData['message'];
            }
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementReadCount()
    {
        $this->stepExecution->incrementReadCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getReadCount()
    {
        return $this->stepExecution->getReadCount();
    }

    /**
     * {@inheritdoc}
     */
    public function incrementAddCount()
    {
        $this->setValue('add_count', (int)$this->getValue('add_count') + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddCount()
    {
        return $this->getValue('add_count');
    }

    /**
     * {@inheritdoc}
     */
    public function incrementUpdateCount()
    {
        $this->setValue('update_count', (int)$this->getValue('update_count') + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateCount()
    {
        return $this->getValue('update_count');
    }

    /**
     * {@inheritdoc}
     */
    public function incrementReplaceCount()
    {
        $this->setValue('replace_count', (int)$this->getValue('replace_count') + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getReplaceCount()
    {
        return $this->getValue('replace_count');
    }

    /**
     * {@inheritdoc}
     */
    public function incrementDeleteCount()
    {
        $this->setValue('delete_count', (int)$this->getValue('delete_count') + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteCount()
    {
        return $this->getValue('delete_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($name, $value)
    {
        $this->stepExecution->getExecutionContext()->put($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($name)
    {
        return $this->stepExecution->getExecutionContext()->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $stepName = $this->stepExecution->getStepName();
        $rawConfiguration = $this->stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();

        return !empty($rawConfiguration[$stepName]) ? $rawConfiguration[$stepName] : $rawConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        $configuration = $this->getConfiguration();
        return isset($configuration[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        if ($this->hasOption($name)) {
            $configuration = $this->getConfiguration();
            return $configuration[$name];
        }
        return $default;
    }
}
