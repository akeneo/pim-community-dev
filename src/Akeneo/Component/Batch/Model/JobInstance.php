<?php

namespace Akeneo\Component\Batch\Model;

use Akeneo\Component\Batch\Job\Job;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entity job
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

    /** @var Job */
    protected $job;

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
     * Set job configuration
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
     * Get raw configuration
     *
     * @return array
     */
    public function getRawConfiguration()
    {
        return $this->rawConfiguration;
    }

    /**
     * Set job
     *
     * @param Job $job
     *
     * @return JobInstance
     */
    public function setJob($job)
    {
        $this->job = $job;

        if ($job) {
            //TODO: FIXME to get only the jobConfiguration instead of merging it
            //with the jobConfiguration:
            //$this->rawConfiguration = $job->getConfiguration();
            // Waiting for the ImportExport fixes on the right uses of the configuration
            // See https://magecore.atlassian.net/browse/BAP-2601
            $jobConfiguration = $job->getConfiguration();

            if (is_array($this->rawConfiguration) && count($this->rawConfiguration) > 0) {
                $this->rawConfiguration = array_merge($this->rawConfiguration, $jobConfiguration);
            } else {
                $this->rawConfiguration = $jobConfiguration;
            }
        }

        return $this;
    }

    /**
     * Get job
     *
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
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
     * Set alias
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
