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
     * {@ineritdoc}
     */
    public function addError($message, $severity = null)
    {
        $severity = (null === $severity) ? ErrorException::CRITICAL : $severity;
        $exception = new ErrorException($message, 0, $severity);
        $this->stepExecution->addFailureException($exception);
    }

    /**
     * {@ineritdoc}
     */
    public function getErrors()
    {
        return $this->stepExecution->getFailureExceptionMessages();
    }

    /**
     * {@ineritdoc}
     */
    public function incrementReadCount()
    {
        $this->stepExecution->incrementReadCount();
    }

    /**
     * {@ineritdoc}
     */
    public function getReadCount()
    {
        return $this->stepExecution->getReadCount();
    }

    /**
     * {@ineritdoc}
     */
    public function incrementAddCount()
    {
        $this->setValue('add_count', (int)$this->getValue('add_count') + 1);
    }

    /**
     * {@ineritdoc}
     */
    public function getAddCount()
    {
        return $this->getValue('add_count');
    }

    /**
     * {@ineritdoc}
     */
    public function incrementUpdateCount()
    {
        $this->setValue('update_count', (int)$this->getValue('update_count') + 1);
    }

    /**
     * {@ineritdoc}
     */
    public function getUpdateCount()
    {
        return $this->getValue('update_count');
    }

    /**
     * {@ineritdoc}
     */
    public function incrementReplaceCount()
    {
        $this->setValue('replace_count', (int)$this->getValue('replace_count') + 1);
    }

    /**
     * {@ineritdoc}
     */
    public function getReplaceCount()
    {
        return $this->getValue('replace_count');
    }

    /**
     * {@ineritdoc}
     */
    public function incrementDeleteCount()
    {
        $this->setValue('delete_count', (int)$this->getValue('delete_count') + 1);
    }

    /**
     * {@ineritdoc}
     */
    public function getDeleteCount()
    {
        return $this->getValue('delete_count');
    }

    /**
     * {@ineritdoc}
     */
    public function setValue($name, $value)
    {
        $this->stepExecution->getExecutionContext()->put($name, $value);
    }

    /**
     * {@ineritdoc}
     */
    public function getValue($name)
    {
        return $this->stepExecution->getExecutionContext()->get($name);
    }

    /**
     * {@ineritdoc}
     */
    public function getConfiguration()
    {
        return $this->stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();
    }
}
