<?php

namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Step\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Batch domain object representing the execution of a job
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobExecution
{
    /* @var JobParameters $jobParameters */
    private $jobParameters;

    /* @var JobInstance $jobInstance */
    private $jobInstance;

    // Collection of StepExecution
    /* @var ArrayCollection $stepExecutions */
    private $stepExecutions;

    /* @var BatchStatus $status */
    private $status;

    private $startTime = null;

    private $createTime = null;

    private $endTime = null;

    private $lastUpdated = null;

    private $exitStatus = null;

    //private volatile ExecutionContext executionContext = new ExecutionContext();

    //private transient volatile List<Throwable> failureExceptions = new CopyOnWriteArrayList<Throwable>();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = new BatchStatus(BatchStatus::STARTING);
        $this->exitStatus = new ExitStatus(ExitStatus::UNKNOWN);
        $this->stepExecutions = new ArrayCollection();
        $this->createTime = time();
    }

    public function setJobParameters($jobParameters)
    {
        $this->jobParameters = $jobParameters;
    }

    /**
     * Return the parameters of the job
     *
     * @return mixed
     */
    public function getJobParameters()
    {
        return $this->jobParameters;
    }

    /**
     * Get end time
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }
    /*
    public void setJobInstance(JobInstance jobInstance) {
        this.jobInstance = jobInstance;
    }
    */

    /**
     * Set end time
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Get start time
     * @return mixed
     */
    public function getStartTime()
    {
        return $startTime;
    }

    /**
     * Set start time
     * @param mixed $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * Get status
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of the status field.
     *
     * @param BatchStatus $status the status to set
     */
    public function setStatus(BatchStatus $status)
    {
        $this->status = $status;
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
        $this->status = $this->status->upgradeTo($status);
    }

    /**
     * @param ExitStatus $exitStatus
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
    }

    /**
     * @return the exitStatus
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
     * @return mixed the time when this execution was created.
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param mixed $createTime creation time of this execution.
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * Add a step executions to job's step execution
     *
     * @param StepExecution $stepExecution
     */
    public function addStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecutions->add($stepExecution);
    }

    /**
     * Get the date representing the last time this JobExecution was updated in
     * the JobRepository.
     *
     * @return Date representing the last time this JobExecution was updated.
     */
    /*
    public Date getLastUpdated() {
        return lastUpdated;
    }
    */

    /**
     * Set the last time this JobExecution was updated.
     *
     * @param lastUpdated
     */
    /*
    public void setLastUpdated(Date lastUpdated) {
        this.lastUpdated = lastUpdated;
    }
    */

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $string = "";
        try {
            $message = "startTime=%s, endTime=%s, lastUpdated=%s, status=%s,"
                . "exitStatus=%s, job=[%s], jobParameters=[%s]";
            $string = sprintf(
                $message,
                $this->startTime,
                $this->endTime,
                $this->lastUpdated,
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
