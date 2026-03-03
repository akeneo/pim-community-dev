<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\EventListener;

use Akeneo\Tool\Bundle\BatchQueueBundle\EventListener\AckMessageEventListener;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class AckMessageEventListenerSpec extends ObjectBehavior
{
    function let(ContainerInterface $receiverLocator)
    {
        $this->beConstructedWith($receiverLocator);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(AckMessageEventListener::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_does_nothing_when_message_is_not_a_job_message(ContainerInterface $receiverLocator)
    {
        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver');

        $receiverLocator->get(Argument::any())->shouldNotBeCalled();

        $this->ackMessage($event);
    }

    function it_acks_the_message_for_a_job_message(
        ContainerInterface $receiverLocator,
        ReceiverInterface $receiver
    ) {
        $envelope = new Envelope(UiJobExecutionMessage::createJobExecutionMessage(1, []));
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver_name');

        $receiverLocator->get('receiver_name')->shouldBeCalledOnce()->willReturn($receiver);
        $receiver->ack($envelope)->shouldBeCalledOnce();

        $this->ackMessage($event);
    }

    function it_acks_the_message_for_a_scheduled_message(
        ContainerInterface $receiverLocator,
        ReceiverInterface $receiver
    ) {
        $envelope = new Envelope(ScheduledJobMessage::createScheduledJobMessage("steven_job", []));
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver_name');

        $receiverLocator->get('receiver_name')->shouldBeCalledOnce()->willReturn($receiver);
        $receiver->ack($envelope)->shouldBeCalledOnce();

        $this->ackMessage($event);
    }
}
