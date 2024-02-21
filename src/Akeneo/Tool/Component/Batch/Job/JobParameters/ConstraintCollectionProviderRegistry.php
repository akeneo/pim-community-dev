<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

/**
 * Registry of constraints that can be used to validate a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConstraintCollectionProviderRegistry
{
    /** @var ConstraintCollectionProviderInterface[] */
    protected $providers = [];

    /**
     * @param ConstraintCollectionProviderInterface $provider
     */
    public function register(ConstraintCollectionProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @param JobInterface $job
     *
     * @throws NonExistingServiceException
     *
     * @return ConstraintCollectionProviderInterface
     */
    public function get(JobInterface $job)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($job)) {
                return $provider;
            }
        }

        throw new NonExistingServiceException(
            sprintf('No constraint collection provider has been defined for the Job "%s"', $job->getName())
        );
    }
}
