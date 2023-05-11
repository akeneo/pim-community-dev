<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ConsumerNameStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\QueueReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerProxyTransport implements TransportInterface, QueueReceiverInterface
{
    public function __construct(
        private readonly array $sendersByMessage,
        private readonly array $receiversByConsumer,
    ) {
    }

    public function getFromQueues(array $queueNames): iterable
    {
        // In Symfony Messenger wording, a "queue" is the equivalent of "consumer" for us.
        // TODO: renaming to avoid confusion ?
        foreach ($queueNames as $consumerName) {
            $receiver = $this->findReceiver($consumerName);
            foreach ($receiver->get() as $envelope) {
                yield $envelope->with(new ConsumerNameStamp($consumerName));
            }
        }
    }

    public function get(): iterable
    {
        throw new \LogicException('This transport is not designed to receive message without specifying consumer name');
    }

    public function ack(Envelope $envelope): void
    {
        $consumerNameStamp = $envelope->last(ConsumerNameStamp::class);
        Assert::notNull($consumerNameStamp, 'The consumer name stamp is mandatory to ack the message');

        $this->findReceiver((string) $consumerNameStamp)->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $consumerNameStamp = $envelope->last(ConsumerNameStamp::class);
        Assert::notNull($consumerNameStamp, 'The consumer name stamp is mandatory to reject the message');

        $this->findReceiver((string) $consumerNameStamp)->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->findSender($envelope)->send($envelope);
    }

    private function findSender(Envelope $envelope): SenderInterface
    {
        $message = $envelope->getMessage();
        Assert::isInstanceOf($message, MessageWrapper::class);

        $messageClass = \get_class($message->message());

        if (!\array_key_exists($messageClass, $this->sendersByMessage)) {
            throw new \Exception(sprintf('No sender found for message %s', $messageClass));
        }

        return $this->sendersByMessage[$messageClass];
    }

    private function findReceiver(string $consumerName): ReceiverInterface
    {
        if (!\array_key_exists($consumerName, $this->receiversByConsumer)) {
            throw new \Exception(sprintf('No receiver found for consumer %s', $consumerName));
        }

        return $this->receiversByConsumer[$consumerName];
    }
}
