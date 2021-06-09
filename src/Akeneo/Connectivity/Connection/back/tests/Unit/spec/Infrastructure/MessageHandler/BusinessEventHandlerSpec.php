<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\MessageHandler;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BusinessEventHandlerSpec extends ObjectBehavior
{
    public function let(
        SendBusinessEventToWebhooksHandler $commandHandler,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith($commandHandler, $eventDispatcher);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(BusinessEventHandler::class);
        $this->shouldImplement(MessageSubscriberInterface::class);
    }

    public function it_handles_a_bulk_event_message(): void
    {
        $this->getHandledMessages()
            ->shouldYield(new \ArrayIterator([BulkEventInterface::class => ['from_transport' => 'webhook']]));
    }

    public function it_executes_a_command_to_process_the_message($commandHandler): void
    {
        $event = new BulkEvent([]);

        $commandHandler->handle(new SendBusinessEventToWebhooksCommand($event))
            ->shouldBeCalled();

        $this->__invoke($event);
    }

    public function it_dispatches_a_message_processed_event_once_the_message_has_been_treated(
        $commandHandler,
        $eventDispatcher
    ): void {
        $event = new BulkEvent([]);

        $commandHandler->handle(new SendBusinessEventToWebhooksCommand($event))
            ->shouldBeCalled();

        $eventDispatcher->dispatch(new MessageProcessedEvent())
            ->shouldBeCalled();

        $this->__invoke($event);
    }
}
