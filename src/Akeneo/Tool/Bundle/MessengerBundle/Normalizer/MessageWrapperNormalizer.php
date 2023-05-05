<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Normalizer;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy\MessageWrapper;
use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
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
    public function __construct(
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer,
    )
    {
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof MessageWrapper;
    }

    public function normalize($messageWrapper, $format = null, array $context = []): array
    {
        Assert::isInstanceOf($messageWrapper, MessageWrapper::class);

        $normalized = [
            'tenant_id' => $messageWrapper->tenantId(),
            'correlation_id' => $messageWrapper->correlationId(),
            'message' => $this->normalizer->normalize($messageWrapper->message()),
            'message_class' => \get_class($messageWrapper->message()),
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
        return MessageWrapper::fromNormalized(
            $this->denormalizer->denormalize($data['message'], $data['message_class'], $format, $context),
            $data['tenant_id'],
            $data['correlation_id'] // TODO: handle creation with correlation_id
        );
    }
}
