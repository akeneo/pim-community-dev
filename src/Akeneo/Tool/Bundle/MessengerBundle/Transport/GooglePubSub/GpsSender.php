<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
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
    public function __construct(
        private Topic $topic,
        private SerializerInterface $serializer,
        private OrderingKeySolver $orderingKeySolver
    ) {
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
