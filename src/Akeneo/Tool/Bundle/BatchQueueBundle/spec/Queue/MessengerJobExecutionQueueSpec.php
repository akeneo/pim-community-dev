<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\MessengerJobExecutionQueue;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerJobExecutionQueueSpec extends ObjectBehavior
{
    function let(MessageBusInterface $bus)
    {
        $this->beConstructedWith($bus);
    }

    function it_is_job_execution_queue_interface()
    {
        $this->shouldImplement(JobExecutionQueueInterface::class);
        $this->shouldBeAnInstanceOf(MessengerJobExecutionQueue::class);
    }

    function it_publishes_job_execution_in_the_queue(MessageBusInterface $bus)
    {
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage(1, []);
        $enveloppe = new Envelope($jobExecutionMessage);
        $bus->dispatch($jobExecutionMessage)->willReturn($enveloppe);
        $bus->dispatch($jobExecutionMessage)->shouldBeCalledOnce();

        $this->publish($jobExecutionMessage);
    }
}
