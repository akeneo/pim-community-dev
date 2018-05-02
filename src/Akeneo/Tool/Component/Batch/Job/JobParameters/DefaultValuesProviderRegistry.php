<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

/**
 * Provides default values provider to build a JobParameters depending on the Job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultValuesProviderRegistry
{
    /** @var DefaultValuesProviderInterface[] */
    protected $providers = [];

    /**
     * @param DefaultValuesProviderInterface $provider
     */
    public function register(DefaultValuesProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @param JobInterface $job
     *
     * @throws NonExistingServiceException
     *
     * @return DefaultValuesProviderInterface
     */
    public function get(JobInterface $job)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($job)) {
                return $provider;
            }
        }

        throw new NonExistingServiceException(
            sprintf('No default values provider has been defined for the Job "%s"', $job->getName())
        );
    }
}
