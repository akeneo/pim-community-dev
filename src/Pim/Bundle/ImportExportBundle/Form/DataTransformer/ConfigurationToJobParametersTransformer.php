<?php

namespace Pim\Bundle\ImportExportBundle\Form\DataTransformer;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms a configuration array to a JobParameters and conversely.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationToJobParametersTransformer implements DataTransformerInterface
{
    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /** @var JobInterface */
    protected $job;

    /**
     * @param JobParametersFactory $jobParametersFactory
     * @param JobInterface         $job
     */
    public function __construct(JobParametersFactory $jobParametersFactory, JobInterface $job)
    {
        $this->jobParametersFactory = $jobParametersFactory;
        $this->job                  = $job;
    }

    /**
     * {@inheritdoc}
     *
     * Transforms a configuration (array) to a job parameters (object).
     */
    public function transform($configuration)
    {
        if (null === $configuration) {
            $configuration = [];
        }

        return $this->jobParametersFactory->create($this->job, $configuration);
    }

    /**
     * {@inheritdoc}
     *
     * Transforms a job parameters (object) to a configuration (array).
     */
    public function reverseTransform($jobParameters)
    {
        return $jobParameters->all();
    }
}
