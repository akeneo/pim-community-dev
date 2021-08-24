<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\Topic;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GpsSender implements SenderInterface
{
    private SerializerInterface $serializer;
    private Topic $topic;
    private OrderingKeySolver $orderingKeySolver;

    public function __construct(Topic $topic, SerializerInterface $serializer, OrderingKeySolver $orderingKeySolver)
    {
        $this->topic = $topic;
        $this->serializer = $serializer;
        $this->orderingKeySolver = $orderingKeySolver;
    }

    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        $message = [
            'data' => $encodedMessage['body'],
            'attributes' => $encodedMessage['headers'],
        ];
        if (null !== $orderingKey = $this->orderingKeySolver->solve($envelope)) {
            $message['orderingKey'] = $orderingKey;
        }

        try {
            $this->topic->publish($message);
        } catch (GoogleException $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }

        return $envelope;
    }
}
