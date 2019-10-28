<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

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

    /**
     * @param NormalizerInterface $jobExecutionStandardNormalizer
     * @param UserContext         $userContext
     */
    public function __construct(NormalizerInterface $jobExecutionStandardNormalizer, UserContext $userContext)
    {
        $this->jobExecutionStandardNormalizer = $jobExecutionStandardNormalizer;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($jobExecution, $format = null, array $context = []): array
    {
        try {
            $timezone = $this->userContext->getUserTimezone();
        } catch (\RuntimeException $exception) {
            return $this->jobExecutionStandardNormalizer->normalize($jobExecution, 'standard', $context);
        }

        return $this->jobExecutionStandardNormalizer->normalize(
            $jobExecution,
            'standard',
            array_merge($context, ['locale' => $this->userContext->getUiLocaleCode(), 'timezone' => $timezone])
        );
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
