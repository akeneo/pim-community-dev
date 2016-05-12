<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

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

    /** @var boolean */
    protected $isStrict;

    /**
     * @param ConstraintCollectionProviderInterface $provider
     * @param boolean                               $isStrict
     */
    public function register(ConstraintCollectionProviderInterface $provider, $isStrict = true)
    {
        $this->providers[] = $provider;
        $this->isStrict = $isStrict;
    }

    /**
     * @param JobInterface $job
     *
     * @return ConstraintCollectionProviderInterface
     *
     * @throws NonExistingServiceException
     */
    public function get(JobInterface $job)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($job)) {
                return $provider;
            }
        }

        // TODO TIP-303: delete this strict mode which is only used for debug purpose
        if ($this->isStrict) {
            throw new NonExistingServiceException(
                sprintf('No constraint provider has been defined for the Job "%s"', $job->getName())
            );
        }

        return new DefaultConstraintCollectionProvider();
    }
}
