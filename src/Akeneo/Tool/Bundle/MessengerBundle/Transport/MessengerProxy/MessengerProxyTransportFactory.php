<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Akeneo\Tool\Component\Messenger\Config\TransportType;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Webmozart\Assert\Assert;

/**
 * Using PubSub transport is different from other transport, because one queue
 * can be consumed by different subscriptions.
 * So a message that need to be consumed by multiple consumers can be sent once in the topic.
 *
 * For other types of transport, a queue can be consumed only once because the messages
 * are removed as soon they are acked.
 * So a message that need to be consumed by multiple consumers need to be sent multiple times,
 * once by each queue.
 *
 * PubSub:
 *  - 1 transport (sender) to send message (= we send a message in a PubSub topic).
 *    This transport cannot receive/treat messages
 *  - 1 transport (receiver) by consumer (= for each subscription)
 *
 *  Doctrine/sync/...:
 *  - 1 transport by consumer. Messages are sent on every transport.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerProxyTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private readonly TransportFactoryInterface $gcpTransportFactory,
        private readonly TransportFactoryInterface $doctrineTransportFactory,
        private readonly TransportFactoryInterface $inMemoryTransportFactory,
        private readonly TransportFactoryInterface $syncTransportFactory,
        private readonly SerializerInterface $messageSerializer,
        private readonly string $googleCloudProject,
        private readonly string $env,
        private readonly array $messengerConfig,
    ) {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
       Assert::notEmpty($this->messengerConfig['queues'] ?? [], 'There must be a Akeneo Messenger configuration');

        $sendersByMessage = [];
        $receiversByConsumer = [];

        foreach ($this->messengerConfig['queues'] as $queueName => $queueConfig) {
            Assert::keyNotExists($sendersByMessage, $queueConfig['message_class'], 'The same message class can not be defined for several queues');

            $transportType = $this->getTransportTypeByEnv($this->env);

            if ($transportType === TransportType::PUB_SUB) {
                $sendersByMessage[$queueConfig['message_class']] = $this->createPubSubSender($queueName);
                foreach ($queueConfig['consumers'] as $consumer) {
                    $receiversByConsumer[$consumer['name']] = $this->createPubSubReceiver($queueName, $consumer['name']);
                }
            } else {
                $transport = match ($transportType) {
                    TransportType::DOCTRINE => $this->createDoctrineTransport($queueName),
                    TransportType::IN_MEMORY => $this->createInMemoryTransport(),
                    TransportType::SYNC => $this->createSyncTransport(), // To remove if not used
                };

                $sendersByMessage[$queueConfig['message_class']] = $transport;
                foreach ($queueConfig['consumers'] as $consumer) {
                    $receiversByConsumer[$consumer['name']] = $transport;
                }
            }
        }

        return new MessengerProxyTransport($sendersByMessage, $receiversByConsumer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'akeneo-messenger-proxy:');
    }

    private function getTransportTypeByEnv(string $env): TransportType
    {
        return match ($env) {
            'behat', 'dev', 'prod', 'test' => TransportType::PUB_SUB,
            'test_fake' => TransportType::IN_MEMORY,
            default => TransportType::DOCTRINE,
        };
    }

    private function createPubSubSender(string $topicName): TransportInterface
    {
        return $this->gcpTransportFactory->createTransport('gps:',
            [
                'project_id' => $this->googleCloudProject,
                'topic_name' => $topicName,
                'auto_setup' => \in_array($this->env, ['dev', 'test', 'test_fake']),
            ], $this->messageSerializer
        );
    }

    private function createPubSubReceiver(string $topicName, string $subscriptionName): TransportInterface
    {
        return $this->gcpTransportFactory->createTransport('gps:',
            [
                'project_id' => $this->googleCloudProject,
                'topic_name' => $topicName,
                'subscription_name' => $subscriptionName,
                'auto_setup' => \in_array($this->env, ['dev', 'test', 'test_fake']),
            ], $this->messageSerializer
        );
    }

    private function createDoctrineTransport(string $queueName): TransportInterface
    {
        return $this->doctrineTransportFactory->createTransport('doctrine://default', [
            'table_name' => 'messenger_messages',
            'queue_name' => $queueName,
            'redeliver_timeout' => 86400,
            'auto_setup' => false,
        ], $this->messageSerializer);
    }

    private function createInMemoryTransport(): TransportInterface
    {
        return $this->inMemoryTransportFactory->createTransport('in-memory://', [], $this->messageSerializer);
    }

    private function createSyncTransport(): TransportInterface
    {
        return $this->syncTransportFactory->createTransport('sync://', [], $this->messageSerializer);
    }
}
