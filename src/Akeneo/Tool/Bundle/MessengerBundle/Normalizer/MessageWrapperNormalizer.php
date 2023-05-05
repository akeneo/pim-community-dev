<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Normalizer;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy\MessageWrapper;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @todo: improve DX on business message normalize/denormailze
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessageWrapperNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param (NormalizerInterface|DenormalizerInterface)[] $normalizers
     */
    public function __construct(private iterable $normalizers)
    {
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof MessageWrapper;
    }

    public function normalize($messageWrapper, $format = null, array $context = []): array
    {
        Assert::isInstanceOf($messageWrapper, MessageWrapper::class);

        $message = $messageWrapper->message();
        $normalizedMessage = null;
        /** @var NormalizerInterface $normalizer */
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsNormalization($message, $format, $context)) {
                $normalizedMessage = $normalizer->normalize($message, $format, $context);
            }
        }

        Assert::notNull($normalizedMessage, \sprintf('Normalizer for "%s" is not found', \get_class($message)));

        $normalized = [
            'tenant_id' => $messageWrapper->tenantId(),
            'correlation_id' => $messageWrapper->correlationId(),
            'message' => $normalizedMessage,
            'message_class' => \get_class($message),
        ];

        return $normalized;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $type === MessageWrapper::class;
    }

    /**
     * @param array $data The normalized message
     */
    public function denormalize(
        $data,
        string $messageClass,
        ?string $format = null,
        array $context = []
    ): MessageWrapper {
        $message = null;
        /** @var DenormalizerInterface $normalizer */
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsDenormalization($data['message'], $data['message_class'], $format)) {
                $message = $normalizer->denormalize($data['message'], $data['message_class'], $format, $context);
            }
        }


        return MessageWrapper::fromNormalized($message, $data['tenant_id'], $data['correlation_id']);
    }
}
