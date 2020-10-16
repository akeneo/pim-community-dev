<?php

namespace Akeneo\Tool\Bundle\MessengerBundle\Serialization;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
    private $serializer;

    /**
     * @param (NormalizerInterface|DenormalizerInterface)[] $normalizers
     */
    public function __construct(iterable $normalizers)
    {
        $this->serializer = new Serializer(
            iterator_to_array((function () use ($normalizers) {
                yield from $normalizers;
            })()),
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

        return new Envelope($message);
    }

    public function encode(Envelope $envelope): array
    {
        return [
            'body' => $this->serializer->serialize($envelope->getMessage(), 'json'),
            'headers' => [
                'class' => \get_class($envelope->getMessage())
            ],
        ];
    }
}
