<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Normalizer\Pqb;

use Akeneo\Catalogs\Domain\ProductSelection\Criterion;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriterionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof Criterion && 'pqb' === $format;
    }

    /**
     * @param array<array-key, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        \assert($object instanceof Criterion);

        return [
            'field' => $object->getField(),
            'operator' => $object->getOperator(),
            'value' => $object->getValue(),
        ];
    }
}
