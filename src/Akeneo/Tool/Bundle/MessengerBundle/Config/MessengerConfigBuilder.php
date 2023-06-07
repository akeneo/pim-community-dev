<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Config;

use Akeneo\Tool\Component\Messenger\Config\TransportType;
use Symfony\Component\Yaml\Yaml;
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
 *  - 1 transport to send message (= we send a message in a PubSub topic).
 *    This transport cannot receive/treat messages
 *  - 1 transport by consumer (= for each subscription)
 *
 *  Doctrine/sync/...:
 *  - 1 transport by consumer. Messages are sent on every transport.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerConfigBuilder
{
    private const CONFIG_FILEPATH = 'config/messages.yml';
    private const CONFIG_FILEPATH_FOR_ENV = 'config/messages_%s.yml';

    private const SERIALIZER = 'akeneo_messenger.envelope.serializer';

    public function __construct(private readonly string $env)
    {
    }

    public static function loadConfig(string $projectDir, string $env): array
    {
        $config = [];
        $configFile = $projectDir . '/' . self::CONFIG_FILEPATH;
        if (\file_exists($configFile)) {
            Assert::fileExists($configFile);
            $config = Yaml::parse(file_get_contents($configFile));
        }

        $configFileForEnv = $projectDir . '/' . \sprintf(self::CONFIG_FILEPATH_FOR_ENV, $env);
        if (\file_exists($configFileForEnv)) {
            $testConfig = Yaml::parse(file_get_contents($configFileForEnv));
            $config['queues'] = \array_merge($config['queues'] ?? [], $testConfig['queues']);
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(string $projectDir, TransportType $transportType): array
    {
        $config = self::loadConfig($projectDir, $this->env);
        if ([] === $config) {
            return [];
        }

        $transports = [];
        $routing = [];

        $allTransportNames = [];
        foreach ($config['queues'] as $queueName => $pimMessageConfig) {
            $transportNames = [];

            if ($transportType === TransportType::PUB_SUB) {
                // Create 1 transport to send event on the topic
                $transportNames[] = $queueName;
                $allTransportNames[$queueName] = ($allTransportNames[$queueName] ?? 0) + 1;
                Assert::keyExists($pimMessageConfig, 'env_var', 'The parameter env_var must be defined to configure the PubSub topic');
                $transports[$queueName] = $this->createPubSubProducerTransport($pimMessageConfig['env_var']);

                // Create 1 transport by subscription/consumer to receive the messages.
                foreach ($pimMessageConfig['consumers'] as $consumer) {
                    Assert::keyExists($consumer, 'env_var', 'The parameter env_var must be defined to configure the PubSub subscription');
                    $transports[$consumer['name']] = $this->createPubSubReceiverTransport(
                        $pimMessageConfig['env_var'],
                        $consumer['env_var']
                    );
                }
            } else {
                // Create 1 queue by consumer
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

            $routing[$pimMessageConfig['message_class']] = $transportNames;
        }

        $duplicateTransportNames = \array_filter($allTransportNames, static fn (int $count) => $count > 1);
        if ([] !== $duplicateTransportNames) {
            throw new \LogicException('These transports are defined more than once: ' . \implode(', ', $duplicateTransportNames));
        }

        return [] !== $transports
            ? ['transports' => $transports, 'routing' => $routing]
            : []
        ;
    }

    /**
     * @return array<string, mixed>
     */
    private function createDoctrineTransport($queueName): array
    {
        return [
            'dsn' => 'doctrine://default',
            'options' => [
                'table_name' => 'messenger_messages',
                'queue_name' => $queueName,
                'redeliver_timeout' => 86400,
                'auto_setup' => false,
            ],
            'serializer' => self::SERIALIZER,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function createPubSubProducerTransport(string $topicEnvVarName): array
    {
        return [
            'dsn' => 'gps:',
            'options' => [
                'project_id' => '%env(GOOGLE_CLOUD_PROJECT)%',
                'topic_name' => sprintf('%%env(default::string:%s)%%', $topicEnvVarName),
                'auto_setup' => \in_array($this->env, ['dev', 'test', 'test_fake']),
            ],
            'serializer' => self::SERIALIZER,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function createPubSubReceiverTransport(string $topicEnvVarName, string $subscriptionEnvVarName): array
    {
        $transport = $this->createPubSubProducerTransport($topicEnvVarName);
        $transport['options']['subscription_name'] = sprintf('%%env(default::string:%s)%%', $subscriptionEnvVarName);

        return $transport;
    }

    /**
     * @return array<string, string>
     */
    private function createInMemoryTransport(): array
    {
        return [
            'dsn' => 'in-memory://',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function createSyncTransport(): array
    {
        return [
            'dsn' => 'sync://',
        ];
    }
}
