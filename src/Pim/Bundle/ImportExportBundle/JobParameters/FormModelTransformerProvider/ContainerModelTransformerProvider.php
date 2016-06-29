<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormModelTransformerProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Pim\Bundle\ImportExportBundle\JobParameters\FormModelTransformerProviderInterface;

/**
 * Provides model transformers for the supported jobs to transform data from the form to the ui
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContainerModelTransformerProvider implements FormModelTransformerProviderInterface
{
    /** @var array */
    protected $supportedJobNames;

    /** @var array */
    protected $modelTransformers;

    /**
     * @param array $supportedJobNames
     * @param array $modelTransformers
     */
    public function __construct(array $supportedJobNames,  array $modelTransformers)
    {
        $this->supportedJobNames = $supportedJobNames;
        $this->modelTransformers = $modelTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormModelTransformers(JobInstance $jobInstance)
    {
        return $this->modelTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
