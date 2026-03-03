<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Tool\Bundle\MessengerBundle\Config\MessengerConfigBuilder;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Message1;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Message2;
use Akeneo\Tool\Component\Messenger\Config\TransportType;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerConfigBuilderIntegration extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => (bool)($_SERVER['APP_DEBUG'] ?? false)]);
    }

    public function test_it_returns_a_messenger_configuration_for_doctrine_transport(): void
    {
        $configBuilder = new MessengerConfigBuilder('test');
        $config = $configBuilder->build(static::getContainer()->getParameter('kernel.project_dir'), TransportType::DOCTRINE);

        $transportNames = $this->getTransportNames($config);
        Assert::assertContains('consumer1', $transportNames);
        Assert::assertContains('consumer2', $transportNames);
        Assert::assertContains('consumer3', $transportNames);
        Assert::assertEqualsCanonicalizing(
            [
                'dsn' => 'doctrine://default',
                'options' => [
                    'table_name' => 'messenger_messages',
                    'queue_name' => 'consumer1',
                    'redeliver_timeout' => 86400,
                    'auto_setup' => false,
                ],
                'serializer' => 'akeneo_messenger.envelope.serializer',
                'retry_strategy' => [
                    'max_retries' => 1,
                ],
            ],
            $this->getTransportConfig($config, 'consumer1')
        );

        Assert::assertEqualsCanonicalizing(['consumer1', 'consumer2'], $this->getRoutingForMessage($config, Message1::class));
        Assert::assertEqualsCanonicalizing(['consumer3', 'failing_consumer'], $this->getRoutingForMessage($config, Message2::class));
    }

    public function test_it_returns_a_messenger_configuration_for_pubsub_transport(): void
    {
        $configBuilder = new MessengerConfigBuilder('test');
        $config = $configBuilder->build(static::getContainer()->getParameter('kernel.project_dir'), TransportType::PUB_SUB);

        $transportNames = $this->getTransportNames($config);
        Assert::assertContains('test_queue1', $transportNames);
        Assert::assertContains('consumer1', $transportNames);
        Assert::assertContains('consumer2', $transportNames);
        Assert::assertContains('test_queue2', $transportNames);
        Assert::assertContains('consumer3', $transportNames);
        Assert::assertEqualsCanonicalizing(
            [
                'dsn' => 'gps:',
                'options' => [
                    'project_id' => '%env(GOOGLE_CLOUD_PROJECT)%',
                    'topic_name' => '%env(default::string:PUBSUB_TOPIC_TEST_QUEUE_1)%',
                    'auto_setup' => true,
                ],
                'serializer' => 'akeneo_messenger.envelope.serializer',
                'retry_strategy' => [
                    'max_retries' => 1,
                ],
            ],
            $this->getTransportConfig($config, 'test_queue1')
        );
        Assert::assertEqualsCanonicalizing(
            [
                'dsn' => 'gps:',
                'options' => [
                    'project_id' => '%env(GOOGLE_CLOUD_PROJECT)%',
                    'topic_name' => '%env(default::string:PUBSUB_TOPIC_TEST_QUEUE_1)%',
                    'auto_setup' => true,
                    'subscription_name' => '%env(default::string:PUBSUB_SUBSCRIPTION_TEST_CONSUMER_1)%',
                ],
                'serializer' => 'akeneo_messenger.envelope.serializer',
                'retry_strategy' => [
                    'max_retries' => 1,
                ],
            ],
            $this->getTransportConfig($config, 'consumer1')
        );

        Assert::assertEqualsCanonicalizing(['test_queue1'], $this->getRoutingForMessage($config, Message1::class));
        Assert::assertEqualsCanonicalizing(['test_queue2'], $this->getRoutingForMessage($config, Message2::class));
    }

    public function test_it_returns_a_messenger_configuration_for_sync_transport(): void
    {
        $configBuilder = new MessengerConfigBuilder('test');
        $config = $configBuilder->build(static::getContainer()->getParameter('kernel.project_dir'), TransportType::SYNC);

        $transportNames = $this->getTransportNames($config);
        Assert::assertContains('consumer1', $transportNames);
        Assert::assertContains('consumer2', $transportNames);
        Assert::assertContains('consumer3', $transportNames);
        Assert::assertEqualsCanonicalizing(
            [
                'dsn' => 'sync://',
                'retry_strategy' => [
                    'max_retries' => 1,
                ],
            ],
            $this->getTransportConfig($config, 'consumer1')
        );

        Assert::assertEqualsCanonicalizing(['consumer1', 'consumer2'], $this->getRoutingForMessage($config, Message1::class));
        Assert::assertEqualsCanonicalizing(['consumer3', 'failing_consumer'], $this->getRoutingForMessage($config, Message2::class));
    }

    public function test_it_returns_a_messenger_configuration_for_in_memory_transport(): void
    {
        $configBuilder = new MessengerConfigBuilder('test');
        $config = $configBuilder->build(static::getContainer()->getParameter('kernel.project_dir'), TransportType::IN_MEMORY);

        $transportNames = $this->getTransportNames($config);
        Assert::assertContains('consumer1', $transportNames);
        Assert::assertContains('consumer2', $transportNames);
        Assert::assertContains('consumer3', $transportNames);
        Assert::assertEqualsCanonicalizing(
            [
                'dsn' => 'in-memory://',
                'retry_strategy' => [
                    'max_retries' => 1,
                ],
            ],
            $this->getTransportConfig($config, 'consumer1')
        );

        Assert::assertEqualsCanonicalizing(['consumer1', 'consumer2'], $this->getRoutingForMessage($config, Message1::class));
        Assert::assertEqualsCanonicalizing(['consumer3', 'failing_consumer'], $this->getRoutingForMessage($config, Message2::class));
    }

    /**
     * @param array<string, mixed> $config
     * @return string[]
     */
    private function getTransportNames(array $config): array
    {
        return \array_keys($config['transports']);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>|null
     */
    private function getTransportConfig(array $config, string $transportName): array|null
    {
        return $config['transports'][$transportName] ?? null;
    }

    /**
     * @param array<string, mixed> $config
     * @return string[]|null
     */
    private function getRoutingForMessage(array $config, string $messageClass): array|null
    {
        return $config['routing'][$messageClass] ?? null;
    }
}
