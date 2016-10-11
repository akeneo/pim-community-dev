<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Normalizer\Structured\JobInstanceNormalizer as BaseNormalizer;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer extends BaseNormalizer
{
    /**  @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeConfiguration(JobInstance $job)
    {
        $configuration = json_encode($job->getRawParameters());

        return $configuration;
    }
}
