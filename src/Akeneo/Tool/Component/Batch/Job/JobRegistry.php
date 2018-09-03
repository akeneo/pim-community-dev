<?php

namespace Akeneo\Tool\Component\Batch\Job;

/**
 * A runtime service registry for registering job by name.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobRegistry
{
    /** @var JobInterface[] */
    protected $jobs = [];

    /** @var JobInterface[][] */
    protected $jobsByType = [];

    /** @var JobInterface[][] */
    protected $jobsByConnector = [];

    /** @var JobInterface[][] */
    protected $jobsByTypeGroupByConnector = [];

    /**
     * @param JobInterface $job
     * @param string       $jobType
     * @param string       $connector
     *
     * @throws DuplicatedJobException
     */
    public function register(JobInterface $job, $jobType, $connector)
    {
        if (isset($this->jobs[$job->getName()])) {
            throw new DuplicatedJobException(
                sprintf('The job "%s" is already registered', $job->getName())
            );
        }
        $this->jobs[$job->getName()] = $job;
        $this->jobsByType[$jobType][$job->getName()] = $job;
        $this->jobsByTypeGroupByConnector[$jobType][$connector][$job->getName()] = $job;
        $this->jobsByConnector[$connector][$job->getName()] = $job;
    }

    /**
     * @param string $jobName
     *
     * @throws UndefinedJobException
     *
     * @return JobInterface
     */
    public function get($jobName)
    {
        if (!isset($this->jobs[$jobName])) {
            throw new UndefinedJobException(
                sprintf('The job "%s" is not registered', $jobName)
            );
        }

        return $this->jobs[$jobName];
    }

    /**
     * @return JobInterface[]
     */
    public function all()
    {
        return $this->jobs;
    }

    /**
     * @param string $jobType
     *
     * @throws UndefinedJobException
     *
     * @return JobInterface
     */
    public function allByType($jobType)
    {
        if (!isset($this->jobsByType[$jobType])) {
            throw new UndefinedJobException(
                sprintf('There is no registered job with the type "%s"', $jobType)
            );
        }

        return $this->jobsByType[$jobType];
    }

    /**
     * @param string $jobType
     *
     * @throws UndefinedJobException
     *
     * @return JobInterface[]
     */
    public function allByTypeGroupByConnector($jobType)
    {
        if (!isset($this->jobsByTypeGroupByConnector[$jobType])) {
            throw new UndefinedJobException(
                sprintf('There is no registered job with the type "%s"', $jobType)
            );
        }

        return $this->jobsByTypeGroupByConnector[$jobType];
    }

    /**
     * @return string[]
     */
    public function getConnectors()
    {
        return array_keys($this->jobsByConnector);
    }
}
