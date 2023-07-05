<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Component\Normalizer;

use Akeneo\Pim\Structure\Bundle\Event\AttributesOptionWereCreatedOrUpdated;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class AttributesOptionWereCreatedOrUpdatedNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inerhitDoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): AttributesOptionWereCreatedOrUpdated
    {
        return AttributesOptionWereCreatedOrUpdated::denormalize($data);
    }

    /**
     * @inerhitDoc
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === AttributesOptionWereCreatedOrUpdated::class;
    }

    /**
     * @inerhitDoc
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, AttributesOptionWereCreatedOrUpdated::class);

        return $object->normalize();
    }

    /**
     * @inerhitDoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof AttributesOptionWereCreatedOrUpdated;
    }
}
