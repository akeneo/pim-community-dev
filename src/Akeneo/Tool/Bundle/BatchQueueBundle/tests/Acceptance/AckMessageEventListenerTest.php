<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\Acceptance;

use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use AkeneoTest\Acceptance\Messenger\InMemorySpyTransport;
use AkeneoTest\Acceptance\Messenger\InMemorySpyTransportFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AckMessageEventListenerTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false, 'environment' => 'test_fake']);
    }

    protected function get(string $service)
    {
        return self::getContainer()->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->ensureKernelShutdown();
    }

    /** @test */
    public function it_acks_the_message(): void
    {
        $message = UiJobExecutionMessage::createJobExecutionMessage(1, []);
        $envelope = new Envelope($message);
        $this->get('messenger.default_bus')->dispatch($message);

        $event = new WorkerMessageReceivedEvent($envelope, 'job');
        $this->get('event_dispatcher')->dispatch($event);

        $transportFactory = $this->get(InMemorySpyTransportFactory::class);
        $transports = $transportFactory->getCreatedTransports();
        self::assertCount(1, $transports);
        $transport = current($transports);

        $events = $transport->releaseEvents();
        self::assertCount(2, $events);
        self::assertSame(InMemorySpyTransport::SEND_EVENT, $events[0]);
        self::assertSame(InMemorySpyTransport::ACK_EVENT, $events[1]);
    }
}
