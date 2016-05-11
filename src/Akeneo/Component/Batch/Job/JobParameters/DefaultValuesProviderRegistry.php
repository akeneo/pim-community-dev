<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

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

    /** @var boolean */
    protected $isStrict;

    /**
     * @param DefaultValuesProviderInterface $parameters
     * @param boolean                    $isStrict
     */
    public function register(DefaultValuesProviderInterface $parameters, $isStrict = true)
    {
        $this->providers[] = $parameters;
        $this->isStrict = $isStrict;
    }

    /**
     * @param JobInterface $job
     *
     * @return DefaultValuesProviderInterface
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

        // TODO TIP-303: to delete only for debug!
        if ($this->isStrict) {
            throw new NonExistingServiceException(
                sprintf('No default value provider has been defined for the Job "%s"', $job->getName())
            );
        }

        return $this->getProviderFromStepElements($job);
    }

    /**
     * Partially ensure the Backward Compatibility with Akeneo PIM <= v1.5
     *
     * @param JobInterface $job
     *
     * @return DefaultValuesProviderInterface
     *
     * @deprecated will be removed in 1.7, please register a DefaultValuesProviderInterface for your job
     */
    private function getProviderFromStepElements(JobInterface $job)
    {
        $defaults = [];
        if (method_exists($job, 'getSteps')) {
            foreach ($job->getSteps() as $step) {
                if (method_exists($step, 'getConfigurableStepElements')) {
                    foreach ($step->getConfigurableStepElements() as $stepElement) {
                        if (method_exists($stepElement, 'getConfigurationFields')) {
                            foreach (array_keys($stepElement->getConfigurationFields()) as $field) {
                                $defaults[$field] = null;
                            }
                        }
                    }
                }
            }
        }

        return new BackwardCompatibleDefaultValuesProvider($defaults);
    }
}
