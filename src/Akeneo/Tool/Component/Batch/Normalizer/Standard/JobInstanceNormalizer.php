<?php

namespace Akeneo\Tool\Component\Batch\Normalizer\Standard;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     *
     * @param JobInstance $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $results = [
            'code'          => $object->getCode(),
            'job_name'      => $object->getJobName(),
            'label'         => $object->getLabel(),
            'connector'     => $object->getConnector(),
            'type'          => $object->getType(),
            'configuration' => $this->normalizeConfiguration($object),
        ];

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobInstance && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Get normalized configuration
     *
     * @param JobInstance $job
     *
     * @return mixed
     */
    protected function normalizeConfiguration(JobInstance $job)
    {
        return $job->getRawParameters();
    }
}
