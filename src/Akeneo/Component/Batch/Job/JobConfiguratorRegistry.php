<?php

namespace Akeneo\Component\Batch\Job;

/**
 * Configure a Job options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobConfiguratorRegistry
{
    /** @var JobConfiguratorInterface[] */
    protected $configurators = [];

    /**
     * @param JobConfiguratorInterface $configurator
     */
    public function addConfigurator(JobConfiguratorInterface $configurator)
    {
        $this->configurators[] = $configurator;
    }

    /**
     * @param JobInterface $job
     *
     * @return JobConfiguratorInterface[]
     */
    public function getConfiguratorsForJob(JobInterface $job)
    {
        foreach ($this->configurators as $configurator) {
            if ($configurator->supports($job)) {
                yield $configurator;
            }
        }
    }
}
