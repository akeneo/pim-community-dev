<?php

namespace Pim\Bundle\ImportExportBundle\Form\DataTransformer;

use Akeneo\Component\Batch\Job\JobParameters;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ConfigurationToJobParametersTransformer implements DataTransformerInterface
{
    /**
     * Transforms an configuration (array) to a job parameters (object)
     *
     * @param  array|null $configuration
     *
     * @return JobParameters
     */
    public function transform($configuration)
    {
        if (null === $configuration) {
            return new JobParameters([]);
        }

        return new JobParameters($configuration);
    }

    /**
     * Transforms a job parameters (object) to a configuration (array).
     *
     * @param  object|null
     *
     * @return array|null
     */
    public function reverseTransform($jobParameters)
    {
        return $jobParameters->getParameters();
    }
}
