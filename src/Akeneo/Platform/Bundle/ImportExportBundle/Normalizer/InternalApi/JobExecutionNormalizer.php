<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetJobExecutionTracking;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $jobExecutionStandardNormalizer;

    /** @var UserContext */
    private $userContext;

    /** @var GetJobExecutionTracking */
    private $getJobExecutionTracking;

    /** @var NormalizerInterface */
    private $jobExecutionTrackingNormalizer;

    public function __construct(
        NormalizerInterface $jobExecutionStandardNormalizer,
        UserContext $userContext,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer
    ) {
        $this->jobExecutionStandardNormalizer = $jobExecutionStandardNormalizer;
        $this->userContext = $userContext;
        $this->getJobExecutionTracking = $getJobExecutionTracking;
        $this->jobExecutionTrackingNormalizer = $jobExecutionTrackingNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($jobExecution, $format = null, array $context = []): array
    {
        try {
            $timezone = $this->userContext->getUserTimezone();
            $context = array_merge(
                $context,
                ['locale' => $this->userContext->getUiLocaleCode(), 'timezone' => $timezone]
            );
        } catch (\RuntimeException $exception) {
        }

        $normalizedJobExecution = $this->jobExecutionStandardNormalizer->normalize(
            $jobExecution,
            'standard',
            $context
        );
        $normalizedJobExecution['tracking'] = $this->jobExecutionTrackingNormalizer->normalize(
            $this->getJobExecutionTracking->execute($jobExecution->getId())
        );

        return $normalizedJobExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($jobExecution, $format = null): bool
    {
        return $jobExecution instanceof JobExecution && 'internal_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
