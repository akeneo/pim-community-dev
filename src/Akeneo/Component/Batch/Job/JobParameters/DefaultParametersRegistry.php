<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

/**
 * Provides default parameters to build a JobParameters
 * For instance, define that a filepath parameter is fulfilled with '/tmp/myfile.csv' by default
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultParametersRegistry
{
    /** @var DefaultParametersInterface[] */
    protected $defaultParameters = [];

    /**
     * @param DefaultParametersInterface $parameters
     */
    public function register(DefaultParametersInterface $parameters)
    {
        $this->defaultParameters[] = $parameters;
    }

    /**
     * @param JobInterface $job
     *
     * @return DefaultParametersInterface
     */
    public function getDefaultParameters(JobInterface $job)
    {
        foreach ($this->defaultParameters as $default) {
            if ($default->supports($job)) {
                return $default;
            }
        }

        return $this->getDefaultParametersFromStepElements($job);
    }

    /**
     * Ensure Backward Compatibility with PIM <= CE-1.5
     *
     * @param JobInterface $job
     *
     * @return array
     *
     * @deprecated will be removed in 1.7, please use a tagged service to define your configuration fields
     */
    private function getDefaultParametersFromStepElements(JobInterface $job)
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

        return new SimpleDefaultParameters($defaults);
    }
}
