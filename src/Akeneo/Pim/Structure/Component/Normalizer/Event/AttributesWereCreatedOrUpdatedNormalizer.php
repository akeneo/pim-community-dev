<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdatedNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inerhitDoc}
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): AttributesWereCreatedOrUpdated
    {
        return AttributesWereCreatedOrUpdated::denormalize($data);
    }

    /**
     * {@inerhitDoc}
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return $type === AttributesWereCreatedOrUpdated::class;
    }

    /**
     * {@inerhitDoc}
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, AttributesWereCreatedOrUpdated::class);

        return $object->normalize();
    }

    /**
     * {@inerhitDoc}
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof AttributesWereCreatedOrUpdated;
    }
}
