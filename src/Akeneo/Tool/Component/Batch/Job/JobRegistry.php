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
    protected array $jobs = [];

    /** @var JobInterface[][] */
    protected array $jobsByType = [];

    /** @var JobInterface[][] */
    protected array $jobsByConnector = [];

    /** @var JobInterface[][] */
    protected array $jobsByTypeGroupByConnector = [];

    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    /**
     * @throws DuplicatedJobException
     */
    public function register(JobInterface $job, string $jobType, string $connector, ?string $featureFlag): void
    {
        if (!$this->jobIsEnabled($featureFlag)) {
            return;
        }

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
     * @throws UndefinedJobException
     */
    public function get(string $jobName): JobInterface
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
     * @throws UndefinedJobException
     *
     * @return JobInterface[]
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

    private function jobIsEnabled(?string $featureFlag): bool
    {
        if (null === $featureFlag) {
            return true;
        }

        return $this->featureFlags->isEnabled($featureFlag);
    }
}
