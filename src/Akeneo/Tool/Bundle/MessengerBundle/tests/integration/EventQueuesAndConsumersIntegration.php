<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Handler1ForMessage1;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Handler1ForMessage2;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Handler2ForMessage1;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\HandlerObserver;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Message1;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Message2;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventQueuesAndConsumersIntegration extends TestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    private MessageBusInterface $bus;
    private string $projectDir;
    private HandlerObserver $handlerObserver;
    /** @var array<PubSubQueueStatus> */
    private array $pubSubQueueStatuses = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->bus = $this->get('messenger.default_bus');
        $this->projectDir = $this->getParameter('kernel.project_dir');
        $this->handlerObserver = $this->get(HandlerObserver::class);
        $this->pubSubQueueStatuses = [
            'consumer1' => $this->get('akeneo_integration_tests.pub_sub_queue_status.consumer1'),
            'consumer2' => $this->get('akeneo_integration_tests.pub_sub_queue_status.consumer2'),
            'consumer3' => $this->get('akeneo_integration_tests.pub_sub_queue_status.consumer3'),
        ];

        foreach ($this->pubSubQueueStatuses as $pubSubQueueStatus) {
            $pubSubQueueStatus->createTopicAndSubscription();
        }

        $this->flushQueues();
        $this->handlerObserver->reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->flushQueues();
        $this->handlerObserver->reset();
    }

    public function test_it_consumes_messages_with_the_right_handler(): void
    {
        $message1 = new Message1('hello');
        $this->bus->dispatch($message1);

        $this->launchConsumer('consumer1');
        Assert::assertSame(1, $this->handlerObserver->getTotalNumberOfExecution());
        Assert::assertSame(1, $this->handlerObserver->getHandlerNumberOfExecution(Handler1ForMessage1::class));
        Assert::assertFalse($this->pubSubQueueStatuses['consumer1']->hasMessageInQueue());
        Assert::assertTrue($this->pubSubQueueStatuses['consumer2']->hasMessageInQueue());
        Assert::assertFalse($this->pubSubQueueStatuses['consumer3']->hasMessageInQueue());
        Assert::assertTrue($this->handlerObserver->messageIsHandledByHandler($message1, Handler1ForMessage1::class));
        Assert::assertFalse($this->handlerObserver->messageIsHandledByHandler($message1, Handler2ForMessage1::class));
        Assert::assertFalse($this->handlerObserver->messageIsHandledByHandler($message1, Handler1ForMessage2::class));

        $this->launchConsumer('consumer2');
        Assert::assertSame(1, $this->handlerObserver->getTotalNumberOfExecution());
        Assert::assertSame(1, $this->handlerObserver->getHandlerNumberOfExecution(Handler2ForMessage1::class));
        Assert::assertFalse($this->pubSubQueueStatuses['consumer1']->hasMessageInQueue());
        Assert::assertFalse($this->pubSubQueueStatuses['consumer2']->hasMessageInQueue());
        Assert::assertFalse($this->pubSubQueueStatuses['consumer3']->hasMessageInQueue());
        Assert::assertTrue($this->handlerObserver->messageIsHandledByHandler($message1, Handler2ForMessage1::class));

        $message2 = new Message2(10);
        $this->bus->dispatch($message2);
        $this->launchConsumer('consumer3');
        Assert::assertSame(1, $this->handlerObserver->getTotalNumberOfExecution());
        Assert::assertSame(1, $this->handlerObserver->getHandlerNumberOfExecution(Handler1ForMessage2::class));
        Assert::assertFalse($this->pubSubQueueStatuses['consumer1']->hasMessageInQueue());
        Assert::assertFalse($this->pubSubQueueStatuses['consumer2']->hasMessageInQueue());
        Assert::assertFalse($this->pubSubQueueStatuses['consumer3']->hasMessageInQueue());
        Assert::assertTrue($this->handlerObserver->messageIsHandledByHandler($message2, Handler1ForMessage2::class));
    }

    private function launchConsumer(string $consumerName): void
    {
        $command = [
            \sprintf('%s/bin/console', $this->projectDir),
            self::MESSENGER_COMMAND_NAME,
            \sprintf('--env=%s', $this->getParameter('kernel.environment')),
            '--limit=1',
            '-vvv',
            \sprintf('--time-limit=%d', 5),
            $consumerName,
            '--bus=ucs_message.handle.bus'
        ];

        $process = new Process($command, null, ['APP_TENANT_ID' => 'pim_test']);
        $process->run();
        $process->wait();

        Assert::assertSame(0, $process->getExitCode(), 'An error occurred: ' . $process->getErrorOutput());
    }

    private function flushQueues(): void
    {
        foreach ($this->pubSubQueueStatuses as $pubSubStatus) {
            $subscription = $pubSubStatus->getSubscription();
            try {
                $subscription->reload();
            } catch (\Exception) {
            }
            if (!$subscription->exists()) {
                continue;
            }

            do {
                $messages = $subscription->pull(['maxMessages' => 10, 'returnImmediately' => true]);
                $count = count($messages);
                if ($count > 0) {
                    $subscription->acknowledgeBatch($messages);
                }
            } while (0 < $count);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
