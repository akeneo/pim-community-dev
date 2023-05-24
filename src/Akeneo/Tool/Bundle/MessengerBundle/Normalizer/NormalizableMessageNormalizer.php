<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Normalizer;

use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @todo To remove
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NormalizableMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof NormalizableMessageInterface;
    }

    /**
     * @param NormalizableMessageInterface $jobExecutionMessage
     */
    public function normalize($message, $format = null, array $context = []): array
    {
        Assert::implementsInterface($message, NormalizableMessageInterface::class);

        $normalized = $message->normalize();
        if ($message instanceof TraceableMessageInterface) {
            $normalized['tenant_id'] = $message->getTenantId();
            $normalized['correlation_id'] = $message->getCorrelationId();
        }

        return $normalized;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_subclass_of($type, NormalizableMessageInterface::class);
    }

    /**
     * @param array $data The normalized message
     */
    public function denormalize(
        $data,
        string $messageClass,
        ?string $format = null,
        array $context = []
    ): NormalizableMessageInterface {
        Assert::classExists($messageClass);
        $object = $messageClass::denormalize($data);
        if ($object instanceof TraceableMessageInterface && null !== ($data['correlation_id'] ?? null)) {
            $object->setCorrelationId($data['correlation_id']);
        }
        if ($object instanceof TraceableMessageInterface && null !== ($data['tenant_id'] ?? null)) {
            $object->setTenantId($data['tenant_id']);
        }

        return $object;
    }
}
