<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyWasUpdatedNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): FamilyWasUpdated
    {
        return FamilyWasUpdated::denormalize($data);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return FamilyWasUpdated::class === $type;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, FamilyWasUpdated::class);

        return $object->normalize();
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof FamilyWasUpdated;
    }
}
