<?php

namespace Akeneo\Bundle\BatchBundle\Entity;

use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Akeneo\Bundle\BatchBundle\Job\ExitStatus;
use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Batch domain object representation the execution of a step. Unlike
 * JobExecution, there are additional properties related the processing
 * of items such as commit count, etc.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="akeneo_batch_step_execution")
 * @ORM\Entity()
 */
class StepExecution
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var JobExecution
     *
     * @ORM\ManyToOne(targetEntity="JobExecution", inversedBy="stepExecutions")
     * @ORM\JoinColumn(name="job_execution_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $jobExecution = null;

    /**
     * @var string
     *
     * @ORM\Column(name="step_name", type="string", length=100, nullable=true)
     * @Assert\NotBlank
     */
    private $stepName;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="read_count", type="integer")
     */
    private $readCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="write_count", type="integer")
     */
    private $writeCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="filter_count", type="integer")
     */
    private $filterCount = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $endTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus */
    private $exitStatus = null;

    /**
     * @var string
     *
     * @ORM\Column(name="exit_code", type="string", length=255, nullable=true)
     */
    private $exitCode = null;

    /**
     * @var string
     *
     * @ORM\Column(name="exit_description", type="text", nullable=true)
     */
    private $exitDescription = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="terminate_only", type="boolean", nullable=true)
     */
    private $terminateOnly = false;

    /**
     * @var array
     *
     * @ORM\Column(name="failure_exceptions", type="array", nullable=true)
     */
    private $failureExceptions = null;

    /**
     * @var array
     *
     * @ORM\Column(name="errors", type="array")
     */
    private $errors = array();

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Warning",
     *      mappedBy="stepExecution",
     *      cascade={"persist", "remove"},
     *      fetch="EXTRA_LAZY",
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $warnings;

    /**
     * @var array
     *
     * @ORM\Column(name="summary", type="array")
     */
    private $summary = array();

    /**
     * Constructor with mandatory properties.
     *
     * @param string       $stepName     the step to which this execution belongs
     * @param JobExecution $jobExecution the current job execution
     */
    public function __construct($stepName, JobExecution $jobExecution)
    {
        $this->stepName = $stepName;
        $this->jobExecution = $jobExecution;
        $jobExecution->addStepExecution($this);
        $this->warnings = new ArrayCollection();
        $this->executionContext = new ExecutionContext();
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::EXECUTING));

        $this->failureExceptions = array();
        $this->errors = array();

        $this->startTime = new \DateTime();
    }

    /**
     * Reset id on clone
     */
    public function __clone()
    {
        $this->id = null;
    }

    /**
     * Get Id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the {@link ExecutionContext} for this execution
     *
     * @return ExecutionContext with its attributes
     */
    public function getExecutionContext()
    {
        return $this->executionContext;
    }

    /**
     * Sets the {@link ExecutionContext} for this execution
     *
     * @param ExecutionContext $executionContext the attributes
     *
     * @return $this
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return \DateTime time that this execution ended
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param \DateTime $endTime the time that this execution ended
     *
     * @return $this
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Returns the current number of items read for this execution
     *
     * @return integer the current number of items read for this execution
     */
    public function getReadCount()
    {
        return $this->readCount;
    }

    /**
     * Sets the current number of read items for this execution
     *
     * @param integer $readCount the current number of read items for this execution
     *
     * @return $this
     */
    public function setReadCount($readCount)
    {
        $this->readCount = $readCount;

        return $this;
    }

    /**
     * Increment the read count by 1
     */
    public function incrementReadCount()
    {
        $this->readCount++;
    }

    /**
     * Returns the current number of items written for this execution
     *
     * @return integer the current number of items written for this execution
     */
    public function getWriteCount()
    {
        return $this->writeCount;
    }

    /**
     * Sets the current number of written items for this execution
     *
     * @param integer $writeCount the current number of written items for this execution
     *
     * @return $this
     */
    public function setWriteCount($writeCount)
    {
        $this->writeCount = $writeCount;

        return $this;
    }

    /**
     * Increment the write count by 1
     */
    public function incrementWriteCount()
    {
        $this->writeCount++;
    }

    /**
     * Returns the current number of items filtered out of this execution
     *
     * @return integer the current number of items filtered out of this execution
     */
    public function getFilterCount()
    {
        return $this->readCount - $this->writeCount;
    }

    /**
     * @return boolean flag to indicate that an execution should halt
     */
    public function isTerminateOnly()
    {
        return $this->terminateOnly;
    }

    /**
     * Set a flag that will signal to an execution environment that this
     * execution (and its surrounding job) wishes to exit.
     *
     * @return $this
     */
    public function setTerminateOnly()
    {
        $this->terminateOnly = true;

        return $this;
    }

    /**
     * Gets the time this execution started
     *
     * @return the time this execution started
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Sets the time this execution started
     *
     * @param \DateTime $startTime the time this execution started
     *
     * @return $this
     */
    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus()
    {
        return new BatchStatus($this->status);
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     *
     * @return $this
     */
    public function setStatus(BatchStatus $status)
    {
        $this->status = $status->getValue();

        return $this;
    }

    /**
     * Upgrade the status field if the provided value is greater than the
     * existing one. Clients using this method to set the status can be sure
     * that they don't overwrite a failed status with an successful one.
     *
     * @param mixed $status the new status value
     *
     * @return $this
     */
    public function upgradeStatus($status)
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @return string the name of the step
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * @param ExitStatus $exitStatus
     *
     * @return $this
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
    }

    /**
     * @return ExitStatus the exit status
     */
    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the execution context information of the enclosing job.
     *
     * @return JobExecution the job execution that was used to start this step execution.
     *
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }

    /**
     * Get failure exceptions
     * @return mixed
     */
    public function getFailureExceptions()
    {
        return $this->failureExceptions;
    }

    /**
     * Add a failure exception
     * @param \Exception $e
     *
     * @return $this
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptions[] = array(
            'class'             => get_class($e),
            'message'           => $e->getMessage(),
            'messageParameters' => $e instanceof RuntimeErrorException ? $e->getMessageParameters() : array(),
            'code'              => $e->getCode(),
            'trace'             => $e->getTraceAsString()
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getFailureExceptionMessages()
    {
        return implode(
            ' ',
            array_map(
                function ($e) {
                    return $e['message'];
                },
                $this->failureExceptions
            )
        );
    }

    /**
     * @param string $message
     *
     * @return StepExecution
     */
    public function addError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add a warning
     *
     * @param string $name
     * @param string $reason
     * @param array  $reasonParameters
     * @param mixed  $item
     */
    public function addWarning($name, $reason, array $reasonParameters, $item)
    {
        $element = $this->stepName;
        if (strpos($element, '.')) {
            $element = substr($element, 0, strpos($element, '.'));
        }
        if (is_object($item)) {
            $item = [
                'class'  => ClassUtils::getClass($item),
                'id'     => method_exists($item, 'getId') ? $item->getId() : '[unknown]',
                'string' => method_exists($item, '__toString') ? (string) $item : '[unknown]',
            ];
        }
        $this->warnings->add(
            new Warning(
                $this,
                sprintf('%s.steps.%s.title', $element, $name),
                $reason,
                $reasonParameters,
                $item
            )
        );
    }

    /**
     * Get the warnings
     *
     * @return ArrayCollection
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Add row in summary
     *
     * @param string $key
     * @param mixed  $info
     */
    public function addSummaryInfo($key, $info)
    {
        $this->summary[$key]= $info;
    }

    /**
     * Increment counter in summary
     *
     * @param string  $key
     * @param integer $increment
     */
    public function incrementSummaryInfo($key, $increment = 1)
    {
        if (!isset($this->summary[$key])) {
            $this->summary[$key]= $increment;
        } else {
            $this->summary[$key]= $this->summary[$key] + $increment;
        }
    }

    /**
     * Get a summary row
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSummaryInfo($key)
    {
        return $this->summary[$key];
    }

    /**
     * Set summary
     *
     * @param array $summary
     *
     * @return \Akeneo\Bundle\BatchBundle\Entity\StepExecution
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return array
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $summary = 'id=%d, name=[%s], status=[%s], exitCode=[%s], exitDescription=[%s]';

        return sprintf(
            $summary,
            $this->id,
            $this->stepName,
            $this->status,
            $this->exitCode,
            $this->exitDescription
        );
    }
}
