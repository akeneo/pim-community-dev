<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Normalizer;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupActivationHasChangedNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): AttributeGroupActivationHasChanged
    {
        return AttributeGroupActivationHasChanged::denormalize($data);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return AttributeGroupActivationHasChanged::class === $type;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, AttributeGroupActivationHasChanged::class);

        return $object->normalize();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof AttributeGroupActivationHasChanged;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
