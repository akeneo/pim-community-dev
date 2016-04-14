<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

/**
 * Provides options to build the JobParameters forms
 * For instance, how to render the filepath parameter in an export context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormsOptionsRegistry
{
    /** @var FormsOptionsInterface[] */
    protected $formsOptions = [];

    /**
     * @param FormsOptionsInterface $options
     */
    public function register(FormsOptionsInterface $options)
    {
        $this->formsOptions[] = $options;
    }

    /**
     * @param JobInterface $job
     *
     * @return FormsOptionsInterface
     */
    public function getFormsOptions(JobInterface $job)
    {
        foreach ($this->formsOptions as $options) {
            if ($options->supports($job)) {
                return $options;
            }
        }

        return $this->getFormsOptionsFromStepElements($job);
    }

    /**
     * Ensure Backward Compatibility with PIM <= CE-1.5 by fetching configuration from getConfigurationFields()
     *
     * @param JobInterface $job
     *
     * @return array
     *
     * @deprecated will be removed in 1.7, please use a tagged service to define your configuration fields
     */
    private function getFormsOptionsFromStepElements(JobInterface $job)
    {
        $options = [];
        if (method_exists($job, 'getSteps')) {
            foreach ($job->getSteps() as $step) {
                if (method_exists($step, 'getConfigurableStepElements')) {
                    foreach ($step->getConfigurableStepElements() as $stepElement) {
                        if (method_exists($stepElement, 'getConfigurationFields')) {
                            $options = array_merge($options, $stepElement->getConfigurationFields());
                        }
                    }
                }
            }
        }

        return new SimpleFormsOptions($options);
    }
}
