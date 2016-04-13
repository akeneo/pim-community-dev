<?php

namespace Akeneo\Component\Batch\Job;

/**
 * Allow to create immutable JobParameters with only default values or with default values and passed parameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParametersFactory
{
    /**
     * @param JobInterface $job
     *
     * @return JobParameters
     */
    public function createDefault(JobInterface $job)
    {
        if ('pim_base_connector.jobs.csv_attribute_export.title' === $job->getName()) {
            $defaults = [
                'filePath' => null,
                'delimiter' => ';',
                'enclosure' => '"',
                'withHeader' => true,
            ];
        } else {
            $defaults = $this->getDefaultParametersFromStepElements($job);
        }

        return new JobParameters($defaults);
    }

    /**
     * @param JobInterface $job
     * @param array        $parameters
     *
     * @return JobParameters
     */
    public function create(JobInterface $job, array $parameters)
    {
        $default = $this->createDefault($job);
        $defaultParams = $default->getParameters();
        $parameters = array_merge($defaultParams, $parameters);

        return new JobParameters($parameters);
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
        $configuration = [];
        if (method_exists($job, 'getSteps')) {
            foreach ($job->getSteps() as $step) {
                if (method_exists($step, 'getConfigurableStepElements')) {
                    foreach ($step->getConfigurableStepElements() as $stepElement) {
                        if (method_exists($stepElement, 'getConfigurationFields')) {
                            foreach (array_keys($stepElement->getConfigurationFields()) as $field) {
                                $configuration[$field] = null;
                            }
                        }
                    }
                }
            }
        }

        return $configuration;
    }
}
