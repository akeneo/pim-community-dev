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
     * @var int
     */
    protected $jobId;

    /**
     * @var string
     */
    protected $jobCode;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @return ContextInterface|null
     */
    public function getContext()
    {
        return $this->context;
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
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @param int $jobId
     * @return JobResult
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
        return $this;
    }

    /**
     * @return int
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param string $jobCode
     * @return JobResult
     */
    public function setJobCode($jobCode)
    {
        $this->jobCode = $jobCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getJobCode()
    {
        return $this->jobCode;
    }
}
