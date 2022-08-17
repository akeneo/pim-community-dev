<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Normalizer\Public;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof Catalog && 'public' === $format;
    }

    /**
     * @param array<array-key, mixed> $context
     * @return array{id: string, name: string, enabled: bool}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        \assert($object instanceof Catalog);

        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'enabled' => $object->isEnabled(),
        ];
    }
}
