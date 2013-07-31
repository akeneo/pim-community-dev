<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

use Pim\Bundle\BatchBundle\Item\ExecutionContext;

/**
 * Batch domain object representing the execution of a job
 *
 * Inspired by Spring Batch  org.springframework.batch.job.JobExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_job_execution")
 * @ORM\Entity()
 */
class JobExecution
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /* @var ArrayCollection $stepExecutions */
    /* TODO: link with the jobExecution entity */
    private $stepExecutions = null;

    /* @var Job $jobExecution */
    /* TODO: link with the jobExecution entity */
    private $job = null;


    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $endTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="create_time", type="datetime", nullable=true)
     */
    private $createTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_time", type="datetime", nullable=true)
     */
    private $updatedTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus $existStatus */
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

    /* @var array */
    private $failureExceptionsObjects = null;

    /**
     * @var string
     *
     * @ORM\Column(name="failure_exceptions", type="text", nullable=true)
     */
    private $failureExceptions = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::UNKNOWN));
        $this->stepExecutions = array();
        $this->createTime = time();
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
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return the time that this execution ended
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param mixed $endTime the time that this execution ended
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

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
     * @param mixed $startTime the time this execution started
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }
    /**
     * Gets the time this execution has been created
     *
     * @return the time this execution has been created
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Sets the time this execution has been created
     *
     * @param mixed $startTime the time this execution has been created
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Gets the time this execution has been updated
     *
     * @return the time this execution has been updated
     */
    public function getUpdatedTime()
    {
        return $this->updatedTime;
    }

    /**
     * Sets the time this execution has been updated
     *
     * @param mixed $startTime the time this execution has been updated
     */
    public function setUpdatedTime($updatedTime)
    {
        $this->updatedTime = $updatedTime;

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
     */
    public function upgradeStatus($status)
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @param ExitStatus $exitStatus
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
    }

    /**
     * @return the exitCode
     */
    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the step executions.
     *
     * @return ArrayCollection the step executions that were registered
     */
    public function getStepExecutions()
    {
        return $this->stepExecutions;
    }

    /**
     * Register a step execution with the current job execution.
     *
     * @param mixed $stepName the name of the step the new execution is associated with
     *
     * @return StepExecution the created stepExecution
     */
    public function createStepExecution($stepName)
    {
        $stepExecution = new StepExecution($stepName, $this);
        $this->stepExecutions->add($stepExecution);

        return $stepExecution;
    }

    /**
     * Add a step executions to job's step execution
     *
     * @param StepExecution $stepExecution
     */
    public function addStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecutions->add($stepExecution);

        return $this;
    }

    /**
     * Test if this JobExecution indicates that it is running. It should
     * be noted that this does not necessarily mean that it has been persisted
     * as such yet.
     *
     * @return true if the end time is null
     */
    public function isRunning()
    {
        return $this->endTime == null;
    }

    /**
     * Test if this JobExecution indicates that it has been signalled to
     * stop.
     * @return true if the status is BatchStatus::STOPPING
     */
    public function isStopping()
    {
        return $this->status->getValue() == BatchStatus::STOPPING;
    }

    /**
     * Signal the JobExecution to stop. Iterates through the associated
     * StepExecution, calling StepExecution::setTerminateOnly().
     *
     */
    public function stop()
    {
        foreach ($this->stepExecutions as $stepExecution) {
            $stepExecution->setTerminateOnly();
        }
        $this->status = new BatchStatus(BatchStatus::STOPPING);
    }

    /**
     * Get failure exceptions
     * @return mixed
     */
    public function getFailureExceptions()
    {
        return $this->failureExceptionsObjects;
    }

    /**
     * Add a failure exception
     * @param Exception $e
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptionsObjects[] = $e;
        $failureExceptions = array();
        foreach ($failureExceptions as $failureException) {
            $failureExceptions[] = array(
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            );
        }

        $this->failureExceptions = serialize($failureExceptions);

        return $this;
    }

    /**
     * Return all failure causing exceptions for this JobExecution, including
     * step executions.
     *
     * @return array containing all exceptions causing failure for
     * this JobExecution.
     */
    public function getAllFailureExceptions()
    {
        $allExceptions = $this->failureExceptions;

        foreach ($this->stepExecutions as $stepExecution) {
            $allExceptions = array_merge($stepExecution->getFailureExceptions());
        }

        return $allExceptions;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $string = "";
        try {
            $message = "startTime=%s, endTime=%s, updatedTime=%s, status=%s,"
                . "exitStatus=%s, job=[%s], jobParameters=[%s]";
            $string = sprintf(
                $message,
                $this->startTime,
                $this->endTime,
                $this->updatedTime,
                $this->status,
                $this->exitStatus,
                $this->jobInstance,
                $this->jobParameters
            );
        } catch (\Exception $e) {
            $string = $e->getMessage();
        }

        return $string;
    }
}
