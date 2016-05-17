<?php

namespace Pim\Bundle\ImportExportBundle\Form\DataTransformer;

use Akeneo\Component\Batch\Job\JobParameters;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transform a configuration array to a JobParameters and conversely
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        return $jobParameters->all();
    }
}
