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
    public function register(JobInterface $job, string $jobType, string $connector): void
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
     * @TODO RAC-267
     * @deprecated
     */
    public function remove($jobName, $jobType, $connector): void
    {
        if (!isset($this->jobs[$jobName])) {
            throw new \InvalidArgumentException(
                sprintf('The job "%s" does not exists', $jobName)
            );
        }

        unset($this->jobs[$jobName]);
        unset($this->jobsByType[$jobType][$jobName]);
        unset($this->jobsByTypeGroupByConnector[$jobType][$connector][$jobName]);
        unset($this->jobsByConnector[$connector][$jobName]);
    }

    /**
     * @param string $jobName
     *
     * @throws UndefinedJobException
     */
    public function get(string $jobName): \Akeneo\Tool\Component\Batch\Job\JobInterface
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
    public function all(): array
    {
        return $this->jobs;
    }

    /**
     * @param string $jobType
     *
     * @throws UndefinedJobException
     */
    public function allByType(string $jobType): array
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
    public function allByTypeGroupByConnector(string $jobType): array
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
    public function getConnectors(): array
    {
        return array_keys($this->jobsByConnector);
    }
}
