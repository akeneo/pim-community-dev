<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Akeneo\Tool\Bundle\MessengerBundle\Config\MessengerConfigBuilder;
use Akeneo\Tool\Component\Messenger\Config\TransportType;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
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
        private readonly string $googleCloudProject,
        private readonly SerializerInterface $messageSerializer,
        private readonly string $env,
        private readonly string $projectDir,
    ) {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        // TODO use cache to not read the YAML file each call ?
        $messengerConfig = MessengerConfigBuilder::loadConfig($this->projectDir, $this->env);

        $sendersByMessage = [];
        $receiversByConsumer = [];

        foreach ($messengerConfig['queues'] as $queueName => $queueConfig) {
            // TODO ensure message class is not already defined for another queue

            $transportType = $this->getTransportTypeByEnv($this->env);

            /**
             * For PubSub we need to create one transport that will only send messages in a topic
             *   And one transport for each consumer (subscription) on this topic to receive the messages
             */
            if ($transportType === TransportType::PUB_SUB) {
                $sendersByMessage[$queueConfig['message_class']] = $this->createPubSubSender($queueName);
                foreach ($queueConfig['consumers'] as $consumer) {
                    $receiversByConsumer[$consumer['name']] = $this->createPubSubReceiver($queueName, $consumer['name']);
                }
            /** For other transport types we use the same transport as sender and receiver */
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
