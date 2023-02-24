<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Config;

use Akeneo\Tool\Component\Messenger\Config\TransportType;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerConfigBuilder
{
    private const CONFIG_FILE = __DIR__ . '/../../../../../../config/events.yml';

    public function __construct(private readonly string $env)
    {
    }

    public static function loadConfig(): array
    {
        Assert::fileExists(self::CONFIG_FILE);

        return Yaml::parse(file_get_contents(self::CONFIG_FILE));
    }

    public function build(TransportType $transportType): array
    {
        $config = self::loadConfig();

        $transports = [];
        $routing = [];

        $allTransportNames = [];
        foreach ($config['queues'] as $queueName => $pimMessageConfig) {
            $transportNames = [];

            if ($transportType === TransportType::PUB_SUB) {
                // Create 1 transport to send event on the topic
                $transportNames[] = $queueName;
                $allTransportNames[$queueName] = ($allTransportNames[$queueName] ?? 0) + 1;
                $transports[$queueName] = $this->createPubSubProducerTransport($queueName, null);

                // Create 1 transport by subscription/consumer to receive the messages.
                foreach ($pimMessageConfig['consumers'] as $consumer) {
                    $transports[$consumer['name']] = $this->createPubSubReceiverTransport($queueName, $consumer['name']);
                }
            } else {
                // Create 1 queue by consumers
                foreach ($pimMessageConfig['consumers'] as $consumer) {
                    $transportNames[] = $consumer['name'];
                    $allTransportNames[$consumer['name']] = ($allTransportNames[$consumer['name']] ?? 0) + 1;
                    $transports[$consumer['name']] = match ($transportType) {
                        TransportType::DOCTRINE => $this->createDoctrineTransport($consumer['name']),
                        TransportType::IN_MEMORY => $this->createInMemoryTransport(),
                        TransportType::SYNC => $this->createSyncTransport(),
                        TransportType::PUB_SUB => throw new \Exception('PubSub cannot be handled here.'),
                    };
                }
            }
            $routing[$pimMessageConfig['messageClass']] = $transportNames;
        }

        $duplicateTransportNames = \array_filter($allTransportNames, static fn (int $count) => $count > 1);
        if ([] !== $duplicateTransportNames) {
            throw new \LogicException('These transports are defined more than once: ' . \implode(', ', $duplicateTransportNames));
        }

        return [
            'transports' => $transports,
            'routing' => $routing,
        ];
    }

    private function createDoctrineTransport($queueName): array {
        return [
            'dsn' => 'doctrine://default',
            'options' => [
                'table_name' => 'messenger_messages',
                'queue_name' => $queueName,
                'redeliver_timeout' => 86400,
                'auto_setup' => false,
            ],
            'serializer' => 'akeneo_batch_queue.messenger.serializer',
        ];
    }

    private function createPubSubProducerTransport(string $topicName): array {
        return [
            'dsn' => 'gps:',
            'options' => \array_filter(
                [
                    'project_id' => '%env(GOOGLE_CLOUD_PROJECT)%',
                    'topic_name' => $topicName,
                    'auto_setup' => \in_array($this->env, ['dev', 'test', 'test_fake']),
                ],
                static fn ($value): bool => null !== $value
            ),
            'serializer' => 'akeneo_batch_queue.messenger.serializer',
        ];
    }

    private function createPubSubReceiverTransport(string $topicName, string $subscriptionName): array {
        $transport = $this->createPubSubProducerTransport($topicName);
        $transport['options']['subscription_name'] = $subscriptionName;

        return $transport;
    }

    private function createInMemoryTransport(): array {
        return [
            'dsn' => 'in-memory://',
        ];
    }

    private function createSyncTransport(): array {
        return [
            'dsn' => 'sync://',
        ];
    }
}
