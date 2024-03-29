<?php

namespace Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Job\Job;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Batch domain object representing a uniquely identifiable configured job.
 *
 * Cf https://docs.spring.io/spring-batch/apidocs/org/springframework/batch/core/JobInstance.html
 *
 * Please note the following difference between Spring Batch and Akeneo Batch,
 *
 * In Spring Batch: a JobInstance can be restarted multiple times in case of execution failure and it's lifecycle ends
 * with first successful execution. Trying to execute an existing JobInstance that has already completed successfully
 * will result in error. Error will be raised also for an attempt to restart a failed JobInstance if the Job is not restartable.
 *
 * In Akeneo Batch: the behavior is not the same, we store a JobInstance, we can run the Job then run it again with the
 * same config, change the config, then run it again.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobInstance
{
    public const STATUS_READY = 0;
    public const STATUS_DRAFT = 1;
    public const STATUS_IN_PROGRESS = 2;

    public const TYPE_IMPORT = 'import';
    public const TYPE_EXPORT = 'export';

    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $jobName;

    /** @var int */
    protected $status = self::STATUS_READY;

    /** @var string */
    protected $connector;

    /**
     * JobInstance type export or import.
     *
     * @var string
     */
    protected $type;

    /** @var array */
    protected $rawParameters = [];

    protected bool $scheduled = false;

    protected ?array $automation = null;

    /** @var Collection|JobExecution[] */
    protected $jobExecutions;

    protected bool $isVisible = true;

    /**
     * Constructor.
     *
     * @param string $connector
     * @param string $type
     * @param string $jobName
     */
    public function __construct($connector = null, $type = null, $jobName = null)
    {
        $this->connector = $connector;
        $this->type = $type;
        $this->jobName = $jobName;
        $this->jobExecutions = new ArrayCollection();
    }

    /**
     * Reset id and clone job executions.
     */
    public function __clone()
    {
        $this->id = null;

        if ($this->jobExecutions) {
            $this->jobExecutions = clone $this->jobExecutions;
        }
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return JobInstance
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return JobInstance
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get connector.
     *
     * @return string
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Get job name.
     *
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return JobInstance
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return JobInstance
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * This parameters can be used to create a JobParameters, stored like this in a legacy way.
     *
     * @param array $rawParameters
     *
     * @return JobInstance
     */
    public function setRawParameters($rawParameters)
    {
        $this->rawParameters = $rawParameters;

        return $this;
    }

    /**
     * This parameters can be used to create a JobParameters, stored like this in a legacy way.
     *
     * @return array
     */
    public function getRawParameters()
    {
        return $this->rawParameters;
    }

    /**
     * @param bool $scheduled
     *
     * @return JobInstance
     */
    public function setScheduled($scheduled)
    {
        $this->scheduled = $scheduled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isScheduled()
    {
        return $this->scheduled;
    }

    public function setAutomation(?array $automation): self
    {
        $this->automation = $automation;

        return $this;
    }

    public function getAutomation(): ?array
    {
        return $this->automation;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /**
     * @return ArrayCollection|JobExecution[]
     */
    public function getJobExecutions()
    {
        return $this->jobExecutions;
    }

    /**
     * @return JobInstance
     */
    public function addJobExecution(JobExecution $jobExecution)
    {
        $this->jobExecutions->add($jobExecution);

        return $this;
    }

    /**
     * @return JobInstance
     */
    public function removeJobExecution(JobExecution $jobExecution)
    {
        $this->jobExecutions->removeElement($jobExecution);

        return $this;
    }

    /**
     * Set job name.
     *
     * Throws logic exception if job name property is already set.
     *
     * @param string $jobName
     *
     * @throws \LogicException
     *
     * @return JobInstance
     */
    public function setJobName($jobName)
    {
        if (null !== $this->jobName) {
            throw new \LogicException('Job name already set in JobInstance');
        }

        $this->jobName = $jobName;

        return $this;
    }

    /**
     * Set connector
     * Throws exception if connector property is already set.
     *
     * @param string $connector
     *
     * @throws \LogicException
     *
     * @return JobInstance
     */
    public function setConnector($connector)
    {
        if (null !== $this->connector) {
            throw new \LogicException('Connector already set in JobInstance');
        }

        $this->connector = $connector;

        return $this;
    }
}
