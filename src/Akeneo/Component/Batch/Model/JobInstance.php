<?php

namespace Akeneo\Component\Batch\Model;

use Akeneo\Component\Batch\Job\Job;
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
    const STATUS_READY       = 0;
    const STATUS_DRAFT       = 1;
    const STATUS_IN_PROGRESS = 2;

    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    /** @var integer */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $alias;

    /** @var integer */
    protected $status = self::STATUS_READY;

    /** @var string */
    protected $connector;

    /**
     * JobInstance type export or import
     *
     * @var string
     */
    protected $type;

    /** @var array */
    protected $rawConfiguration = array();

    /** @var Collection|JobExecution[] */
    protected $jobExecutions;

    /**
     * Constructor
     *
     * @param string $connector
     * @param string $type
     * @param string $alias
     */
    public function __construct($connector = null, $type = null, $alias = null)
    {
        $this->connector     = $connector;
        $this->type          = $type;
        $this->alias         = $alias;
        $this->jobExecutions = new ArrayCollection();
    }

    /**
     * Reset id and clone job executions
     */
    public function __clone()
    {
        $this->id = null;

        if ($this->jobExecutions) {
            $this->jobExecutions = clone $this->jobExecutions;
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
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
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set label
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
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get connector
     *
     * @return string
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * TODO TIP-303: job name in spring batch, may be changed once TIP-384 merged
     *
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return JobInstance
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set type
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * This configuration can be used to create a JobParameters, stored like this in a legacy way
     * TODO TIP-303: we should rename and extract this configuration to be able to use JobParameters from whatever source
     *
     * @param array $configuration
     *
     * @return JobInstance
     */
    public function setRawConfiguration($configuration)
    {
        $this->rawConfiguration = $configuration;

        return $this;
    }

    /**
     * This configuration can be used to create a JobParameters, stored like this in a legacy way
     * TODO TIP-303: we should rename and extract this configuration to be able to use JobParameters from whatever source
     *
     * @return array
     */
    public function getRawConfiguration()
    {
        return $this->rawConfiguration;
    }

    /**
     * TODO TIP-303: such weird, we should only provide the "job name"
     *
     * Set job
     *
     * @param Job $job
     *
     * @return JobInstance
     *
     * @deprecated will be removed in 1.7, this has been used to configure the job instance in a weird way to be able
     *             to access to the Job from the JobInstance in an execution context only, to access to the Job, we
     *             must use ConnectorRegistry->getJob($jobInstance)
     */
    public function setJob($job)
    {
        trigger_error('please use ConnectorRegistry->getJob($jobInstance) instead', E_USER_NOTICE);
    }

    /**
     * Get job
     *
     * @return Job
     *
     * @deprecated will be removed in 1.7, this has been used to configure the job instance in a weird way to be able
     *             to access to the Job from the JobInstance in an execution context only, to access to the Job, we
     *             must use ConnectorRegistry->getJob($jobInstance)
     */
    public function getJob()
    {
        trigger_error('please use ConnectorRegistry->getJob($jobInstance) instead', E_USER_NOTICE);
    }

    /**
     * @return ArrayCollection|JobExecution[]
     */
    public function getJobExecutions()
    {
        return $this->jobExecutions;
    }

    /**
     * @param JobExecution $jobExecution
     *
     * @return JobInstance
     */
    public function addJobExecution(JobExecution $jobExecution)
    {
        $this->jobExecutions->add($jobExecution);

        return $this;
    }

    /**
     * @param JobExecution $jobExecution
     *
     * @return JobInstance
     */
    public function removeJobExecution(JobExecution $jobExecution)
    {
        $this->jobExecutions->removeElement($jobExecution);

        return $this;
    }

    /**
     * TODO TIP-303: job name in spring batch, may be changed once TIP-384 merged
     *
     * Set alias
     *
     * Throws logic exception if alias property is already set.
     *
     * @param string $alias
     *
     * @throws \LogicException
     *
     * @return JobInstance
     */
    public function setAlias($alias)
    {
        if ($this->alias !== null) {
            throw new \LogicException('Alias already set in JobInstance');
        }

        $this->alias = $alias;

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
        if ($this->connector !== null) {
            throw new \LogicException('Connector already set in JobInstance');
        }

        $this->connector = $connector;

        return $this;
    }
}
