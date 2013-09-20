<?php

namespace Oro\Bundle\ImportExportBundle\Job;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class JobResult
{
    /**
     * @var boolean
     */
    protected $successful;

    /**
     * @var ContextInterface[]
     */
    protected $contexts = array();

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @return ContextInterface[]
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->successful;
    }

    /**
     * @param boolean $successful
     * @return JobResult
     */
    public function setSuccessful($successful)
    {
        $this->successful = $successful;
        return $this;
    }

    /**
     * @param string $error
     * @return JobResult
     */
    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @param ContextInterface $context
     * @return JobResult
     */
    public function addContext(ContextInterface $context)
    {
        $this->contexts[] = $context;
        return $this;
    }
}
