<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return LaunchProductAndProductModelEvaluationsMessage::denormalize($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === LaunchProductAndProductModelEvaluationsMessage::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, LaunchProductAndProductModelEvaluationsMessage::class);

        return $object->normalize();
    }

    public function supportsNormalization($data, string $format = null)
    {
        return LaunchProductAndProductModelEvaluationsMessage::class === \get_class($data);
    }

}
