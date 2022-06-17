<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Normalizer\Internal;

use Akeneo\Catalogs\Domain\ProductSelection\Criterion;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriterionDenormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return Criterion::class === $type && 'internal' === $format;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Criterion
    {
        return new Criterion(
            $data['field'],
            $data['operator'],
            $data['value'] ?? null,
        );
    }
}
