<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\NativeMessageStamp;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GpsReceiver implements ReceiverInterface
{
    private const ACKNOWLEDGE_DEADLINE_IN_SECONDS = 600;

    private SerializerInterface $serializer;
    private Subscription $subscription;

    public function __construct(Subscription $subscription, SerializerInterface $serializer)
    {
        $this->subscription = $subscription;
        $this->serializer = $serializer;
    }

    public function get(): iterable
    {
        try {
            $messages = $this->subscription->pull([
                'maxMessages' => 1,
                'returnImmediately' => true,
            ]);
        } catch (GoogleException $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }

        if (0 === count($messages)) {
            return [];
        }

        $message = $messages[0];
        $envelope = $this->serializer->decode([
            'body' => $message->data(),
            'headers' => $message->attributes(),
        ]);

        return [
            $envelope
                ->with(new TransportMessageIdStamp($message->id()))
                ->with(new NativeMessageStamp($message))
        ];
    }

    public function ack(Envelope $envelope): void
    {
        try {
            $this->subscription->acknowledge($this->getNativeMessage($envelope));
        } catch (GoogleException $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }
    }

    public function modifyAckDeadline(Envelope $envelope): void
    {
        try {
            $this->subscription->modifyAckDeadline(
                $this->getNativeMessage($envelope),
                self::ACKNOWLEDGE_DEADLINE_IN_SECONDS
            );
        } catch (GoogleException $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }
    }

    public function reject(Envelope $envelope): void
    {
        try {
            $this->subscription->acknowledge($this->getNativeMessage($envelope));
        } catch (GoogleException $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }
    }

    private function getNativeMessage(Envelope $envelope): Message
    {
        /** @var NativeMessageStamp|null $nativeMessageStamp */
        if (null === $nativeMessageStamp = $envelope->last(NativeMessageStamp::class)) {
            throw new \LogicException('NativeMessageStamp should be present on the Envelope.');
        }

        return $nativeMessageStamp->getNativeMessage();
    }
}
