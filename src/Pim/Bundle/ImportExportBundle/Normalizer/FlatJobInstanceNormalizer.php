<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatJobInstanceNormalizer extends JobInstanceNormalizer
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
