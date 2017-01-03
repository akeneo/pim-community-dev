<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\NonExistingServiceException;

/**
 * Provides options to build the JobParameters forms
 * For instance, how to render the filepath parameter in an export context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormConfigurationProviderRegistry
{
    /** @var FormConfigurationProviderInterface[] */
    protected $formsOptions = [];

    /**
     * @param FormConfigurationProviderInterface $options
     */
    public function register(FormConfigurationProviderInterface $options)
    {
        $this->formsOptions[] = $options;
    }

    /**
     * @param JobInterface $job
     *
     * @throws NonExistingServiceException
     *
     * @return FormConfigurationProviderInterface
     */
    public function get(JobInterface $job)
    {
        foreach ($this->formsOptions as $options) {
            if ($options->supports($job)) {
                return $options;
            }
        }

        throw new NonExistingServiceException(
            sprintf('No form configuration provider has been defined for the Job "%s"', $job->getName())
        );
    }
}
