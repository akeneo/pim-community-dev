<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\NonExistingServiceException;

/**
 * Provides model transformers to add to the JobParameters forms
 * For instance, how to transform a filter field for product export
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormModelTransformerProviderRegistry
{
    /** @var FormModelTransformerProviderInterface[] */
    protected $formsModelTransformerProviders = [];

    /**
     * @param FormModelTransformerProviderInterface $modelTransformerProvider
     */
    public function register(FormModelTransformerProviderInterface $modelTransformerProvider)
    {
        $this->formsModelTransformerProviders[] = $modelTransformerProvider;
    }

    /**
     * @param JobInterface $job
     *
     * @return FormModelTransformerProviderInterface|null
     */
    public function get(JobInterface $job)
    {
        foreach ($this->formsModelTransformerProviders as $modelTransformerProvider) {
            if ($modelTransformerProvider->supports($job)) {
                return $modelTransformerProvider;
            }
        }

        return null;
    }
}
