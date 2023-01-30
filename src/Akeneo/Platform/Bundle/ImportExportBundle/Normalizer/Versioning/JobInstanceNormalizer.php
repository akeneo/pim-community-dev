<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\Versioning;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypterRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a job instance entity into a array.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected array $supportedFormats = ['flat'];

    public function __construct(
        private CredentialsEncrypterRegistry $credentialsEncrypterRegistry
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    public function normalize($jobInstance, $format = null, array $context = [])
    {
        $parameters = $jobInstance->getRawParameters();
        if (isset($parameters['storage'])) {
            $parameters['storage'] = $this->credentialsEncrypterRegistry->obfuscateCredentials($parameters['storage']);
        }

        return [
            'code' => $jobInstance->getCode(),
            'job_name' => $jobInstance->getJobName(),
            'label' => $jobInstance->getLabel(),
            'connector' => $jobInstance->getConnector(),
            'type' => $jobInstance->getType(),
            'configuration' => json_encode($parameters),
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
