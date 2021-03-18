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
    private const ACK_MESSAGE_RIGHT_AFTER_PULL_OPTION = 'ack_message_right_after_pull';
    public const AVAILABLE_OPTIONS = [self::ACK_MESSAGE_RIGHT_AFTER_PULL_OPTION];

    private SerializerInterface $serializer;
    private Subscription $subscription;
    private array $options;

    public function __construct(Subscription $subscription, SerializerInterface $serializer, array $options = [])
    {
        $this->subscription = $subscription;
        $this->serializer = $serializer;
        $this->options = $options;
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
        if ($this->ackMessageRightAfterPullMode()) {
            $this->ackMessage($message);
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
        if ($this->ackMessageRightAfterPullMode()) {
            return;
        }

        $this->ackMessage($this->getNativeMessage($envelope));
    }

    private function ackMessage(Message $message): void
    {
        try {
            $this->subscription->acknowledge($message);
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
        /** @var NativeMessageStamp */
        if (null === $nativeMessageStamp = $envelope->last(NativeMessageStamp::class)) {
            throw new \LogicException('NativeMessageStamp should be present on the Envelope.');
        }

        return $nativeMessageStamp->getNativeMessage();
    }

    private function ackMessageRightAfterPullMode(): bool
    {
        return $this->options[static::ACK_MESSAGE_RIGHT_AFTER_PULL_OPTION] ?? false;
    }
}
