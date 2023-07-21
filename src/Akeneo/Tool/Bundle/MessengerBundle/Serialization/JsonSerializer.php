<?php

namespace Akeneo\Tool\Bundle\MessengerBundle\Serialization;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use UnexpectedValueException;

/**
 * Encodes and decodes an envelope to/from a JSON format.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonSerializer implements SerializerInterface
{
    private Serializer $serializer;

    /**
     * @param (NormalizerInterface|DenormalizerInterface)[] $normalizers
     */
    public function __construct(iterable $normalizers)
    {
        $this->serializer = new Serializer(
            iterator_to_array(
                (function () use ($normalizers) {
                    yield from $normalizers;
                })()
            ),
            [new JsonEncoder()]
        );
    }

    /**
     * Expected header keys:
     * - `class` (string) - The FQCN of the Message object to instanciate
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        if (empty($encodedEnvelope['body']) || empty($encodedEnvelope['headers'])) {
            throw new MessageDecodingFailedException(
                'Encoded envelope should have at least a "body" and some "headers".'
            );
        }

        if (empty($encodedEnvelope['headers']['class'])) {
            throw new MessageDecodingFailedException(
                'Encoded envelope does not have a "class" header.'
            );
        }

        try {
            $message = $this->serializer->deserialize(
                $encodedEnvelope['body'],
                $encodedEnvelope['headers']['class'],
                'json'
            );
        } catch (UnexpectedValueException $e) {
            throw new MessageDecodingFailedException(
                sprintf('Could not decode message: %s.', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        $tenantId = $encodedEnvelope['headers']['tenant_id'] ?? null;
        $correlationId = $encodedEnvelope['headers']['correlation_id'] ?? null;
        $retryCount = $encodedEnvelope['headers']['retry_count'] ?? null;

        $stamps = [];
        if (null !== $tenantId) {
            $stamps[] = new TenantIdStamp($tenantId);
        }
        if (null !== $correlationId) {
            $stamps[] = new CorrelationIdStamp($correlationId);
        }
        if (null !== $retryCount) {
            $stamps[] = new RedeliveryStamp((int) $retryCount);
        }

        return new Envelope($message, $stamps);
    }

    public function encode(Envelope $envelope): array
    {
        $body = $this->serializer->serialize($envelope->getMessage(), 'json');
        $headers = [
            'class' => $envelope->getMessage()::class,
        ];

        /** @var TenantIdStamp|null $tenantIdStamp */
        $tenantIdStamp = $envelope->last(TenantIdStamp::class);

        if (null !== $tenantId = $tenantIdStamp?->pimTenantId()) {
            $headers['tenant_id'] = $tenantId;
        }

        /** @var CorrelationIdStamp|null $correlationIdStamp */
        $correlationIdStamp = $envelope->last(CorrelationIdStamp::class);
        if (null !== $correlationIdStamp) {
            $headers['correlation_id'] = $correlationIdStamp->correlationId();
        }

        /** @var RedeliveryStamp|null $redeliveryStamp */
        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);
        if (null !== $redeliveryStamp) {
            $headers['retry_count'] = (string) $redeliveryStamp->getRetryCount();
        }

        return [
            'body' => $body,
            'headers' => $headers,
        ];
    }
}
