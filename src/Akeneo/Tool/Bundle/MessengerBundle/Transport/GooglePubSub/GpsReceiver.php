<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\NativeMessageStamp;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GpsReceiver implements ReceiverInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var Subscription */
    private $subscription;

    public function __construct(Subscription $subscription, SerializerInterface $serializer)
    {
        $this->subscription = $subscription;
        $this->serializer = $serializer;
    }

    public function get(): iterable
    {
        $message = $this->pullMessage();
        if (null === $message) {
            return [];
        }

        // We dont want to retry messages.
        if (null !== $message->deliveryAttempt() && $message->deliveryAttempt() > 0) {
            $this->rejectMessage($message);

            return $this->get();
        }

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
        $this->subscription->acknowledge($this->getNativeMessage($envelope));
    }

    public function reject(Envelope $envelope): void
    {
        $this->rejectMessage($this->getNativeMessage($envelope));
    }

    private function pullMessage(): ?Message
    {
        $messages = $this->subscription->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ]);
        if (0 === count($messages)) {
            return null;
        }

        return $messages[0];
    }

    /**
     * Resets the acknowledge deadline for the message without acknowledging it.
     * This will make the message available for redelivery.
     */
    private function rejectMessage(Message $message): void
    {
        $this->subscription->modifyAckDeadline($message, 0);
    }

    private function getNativeMessage(Envelope $envelope): Message
    {
        /** @var NativeMessageStamp */
        if (null === $nativeMessageStamp = $envelope->last(NativeMessageStamp::class)) {
            throw new \LogicException('NativeMessageStamp should be present on the Envelope.');
        }

        return $nativeMessageStamp->getNativeMessage();
    }
}
