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
        private TransportFactoryInterface $gcpTransportFactory,
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

            if ($transportType === TransportType::PUB_SUB) {
                $sendersByMessage[$queueConfig['message_class']] = $this->createPubSubSender($queueName);
                foreach ($queueConfig['consumers'] as $consumer) {
                    $receiversByConsumer[$consumer['name']] = $this->createPubSubReceiver($queueName, $consumer['name']);
                }
            } else {
                throw new \Exception('Only PubSub is implemented');
//                $transport = match ($transportType) {
//                    TransportType::DOCTRINE => $this->createDoctrineTransport(),
//                    TransportType::IN_MEMORY => $this->createInMemoryTransport(),
//                    TransportType::SYNC => $this->createSyncTransport(),
//                };
//                $sendersByMessage[$queueConfig['message_class']] = $transport;
//                $receiversByConsumer[$queueConfig['message_class']] = $transport;
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
                'auto_setup' => true, //\in_array($this->env, ['dev', 'test', 'test_fake']),
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
                'auto_setup' => true, //\in_array($this->env, ['dev', 'test', 'test_fake']),
            ], $this->messageSerializer
        );
    }
}
