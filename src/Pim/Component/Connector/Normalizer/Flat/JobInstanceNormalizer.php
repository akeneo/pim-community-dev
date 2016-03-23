<?php

namespace Pim\Component\Connector\Normalizer\Flat;

use Akeneo\Component\Batch\Model\JobInstance;
use Pim\Component\Catalog\Normalizer\Structured\JobInstanceNormalizer as BaseNormalizer;

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
