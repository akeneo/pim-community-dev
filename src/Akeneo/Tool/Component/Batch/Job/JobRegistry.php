<?php

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

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
     * @param FeatureFlags $featureFlags
     */
    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    /**
     * @param JobInterface $job
     * @param string       $jobType
     * @param string       $connector
     *
     * @throws DuplicatedJobException
     */
    public function register(JobInterface $job, $jobType, $connector, $feature = null)
    {
        if (isset($this->jobs[$job->getName()])) {
            throw new DuplicatedJobException(
                sprintf('The job "%s" is already registered', $job->getName())
            );
        }
        $this->jobs[$job->getName()] = [
            'job' => $job,
            'type' => $jobType,
            'connector' => $connector,
            'feature' => $feature,
        ];
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

        return $this->jobs[$jobName]['job'];
    }

    /**
     *
     * @throws UndefinedJobException
     */
    public function isEnabled(string $jobName): bool
    {
        if (!isset($this->jobs[$jobName])) {
            throw new UndefinedJobException(
                sprintf('The job "%s" is not registered', $jobName)
            );
        }
        $feature = $this->jobs[$jobName]['feature'];

        return null === $feature || $this->featureFlags->isEnabled($feature);
    }

    public function has(string $jobName): bool
    {
        return isset($this->jobs[$jobName]);
    }

    /**
     * @return JobInterface[]
     */
    public function all()
    {
        return array_map(static fn (array $job) => $job['job'], $this->getAllEnabledJobs());
    }

    /**
     * @param string $jobType
     *
     * @throws UndefinedJobException
     *
     * @return JobInterface[]
     */
    public function allByType($jobType)
    {
        $jobs = array_filter(
            $this->getAllEnabledJobs(),
            function ($job) use ($jobType) {
                return $job['type'] === $jobType;
            }
        );

        if (empty($jobs)) {
            throw new UndefinedJobException(
                sprintf('There is no registered job with the type "%s"', $jobType)
            );
        }

        return array_map(static fn (array $job) => $job['job'], $jobs);
    }

    /**
     * @param string $jobType
     *
     * @throws UndefinedJobException
     *
     * @return JobInterface[][]
     */
    public function allByTypeGroupByConnector($jobType)
    {
        $jobs = array_filter($this->getAllEnabledJobs(), fn ($job) => $job['type'] === $jobType);

        if (empty($jobs)) {
            throw new UndefinedJobException(
                sprintf('There is no registered job with the type "%s"', $jobType)
            );
        }

        return array_reduce(
            $jobs,
            function ($groupedJobs, $job) {
                $groupedJobs[$job['connector']][$job['job']->getName()] = $job['job'];

                return $groupedJobs;
            },
            []
        );
    }

    /**
     * @return string[]
     */
    public function getConnectors()
    {
        return array_unique(array_map(static fn (array $job) => $job['connector'], $this->getAllEnabledJobs()));
    }

    private function getAllEnabledJobs()
    {
        return array_filter(
            $this->jobs,
            fn (array $job) => null === $job['feature'] || $this->featureFlags->isEnabled($job['feature'])
        );
    }
}
