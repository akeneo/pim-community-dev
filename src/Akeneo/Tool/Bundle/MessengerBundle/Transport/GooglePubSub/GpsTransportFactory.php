<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GpsTransportFactory implements TransportFactoryInterface
{
    private PubSubClientFactory $pubSubClientFactory;
    private OrderingKeySolver $orderingKeySolver;

    public function __construct(PubSubClientFactory $pubSubClientFactory, OrderingKeySolver $orderingKeySolver)
    {
        $this->pubSubClientFactory = $pubSubClientFactory;
        $this->orderingKeySolver = $orderingKeySolver;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $client = Client::fromDsn($this->pubSubClientFactory, $dsn, $options);

        $sender = new GpsSender($client->getTopic(), $serializer, $this->orderingKeySolver);

        $receiver = null;
        if (null !== $subscription = $client->getSubscription()) {
            $receiver = new GpsReceiver($subscription, $serializer);
        }

        return new GpsTransport($client, $sender, $receiver);
    }

    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'gps:');
    }
}
