<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Normalizer;

use Akeneo\Pim\Platform\Messaging\Domain\SerializableMessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof SerializableMessageInterface;
    }

    /**
     * @param SerializableMessageInterface $jobExecutionMessage
     */
    public function normalize($message, $format = null, array $context = []): array
    {
        Assert::implementsInterface($message, SerializableMessageInterface::class);

        return $message->normalize();
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_subclass_of($type, SerializableMessageInterface::class);
    }

    /**
     * @param array $data The normalized message
     */
    public function denormalize(
        $data,
        string $messageClass,
        ?string $format = null,
        array $context = []
    ): SerializableMessageInterface {
        return $messageClass::denormalize($data);
    }
}
