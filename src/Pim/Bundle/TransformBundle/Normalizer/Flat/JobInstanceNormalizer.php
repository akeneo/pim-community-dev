<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Component\Batch\Model\JobInstance;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer extends \Pim\Component\Catalog\Normalizer\JobInstanceNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeConfiguration(JobInstance $job)
    {
        $configuration = json_encode($job->getRawConfiguration());

        return $configuration;
    }
}
