<?php

namespace Akeneo\Component\Batch\Job;

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

    /**
     * @param JobInterface $job
     *
     * @throws DuplicatedJobException
     */
    public function register(JobInterface $job)
    {
        if (isset($this->jobs[$job->getName()])) {
            throw new DuplicatedJobException(
                sprintf('The job "%s" is already registered', $job->getName())
            );
        }
        $this->jobs[$job->getName()] = $job;
    }

    /**
     * @param string $jobName
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
}
