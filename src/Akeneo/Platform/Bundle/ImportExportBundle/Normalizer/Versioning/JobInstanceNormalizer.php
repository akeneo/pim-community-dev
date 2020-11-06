<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\Versioning;

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
    /**  @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $jobInstance
     */
    public function normalize($jobInstance, $format = null, array $context = []): array
    {
        return [
            'code'          => $jobInstance->getCode(),
            'job_name'      => $jobInstance->getJobName(),
            'label'         => $jobInstance->getLabel(),
            'connector'     => $jobInstance->getConnector(),
            'type'          => $jobInstance->getType(),
            'configuration' => json_encode($jobInstance->getRawParameters()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof JobInstance && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
