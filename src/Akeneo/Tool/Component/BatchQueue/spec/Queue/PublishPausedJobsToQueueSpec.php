<?php

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\GetPausedJobExecutionIdsInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishPausedJobsToQueue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class PublishPausedJobsToQueueSpec extends ObjectBehavior
{
    public function let(
        JobExecutionQueueInterface $jobExecutionQueue,
        GetPausedJobExecutionIdsInterface $getPausedJobExecutionIds,
        LoggerInterface $logger,
    ): void {
        $this->beConstructedWith(
            $jobExecutionQueue,
            $getPausedJobExecutionIds,
            $logger,
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PublishPausedJobsToQueue::class);
    }

    public function it_publishes_a_paused_job_to_the_execution_queue(
        JobExecutionQueueInterface $jobExecutionQueue,
        GetPausedJobExecutionIdsInterface $getPausedJobExecutionIds,
    ): void {
        $getPausedJobExecutionIds->all()->willReturn([1, 9]);
        $jobExecutionQueue->publish(Argument::any())->shouldBeCalledTimes(2);

        $this->publish();
    }

    public function it_does_not_fail_when_an_error_occurs_trying_to_publish_a_job(
        JobExecutionQueueInterface $jobExecutionQueue,
        GetPausedJobExecutionIdsInterface $getPausedJobExecutionIds,
        LoggerInterface $logger,
    ): void {
        $getPausedJobExecutionIds->all()->willReturn([1]);
        $jobExecutionQueue->publish(Argument::any())->shouldBeCalled()->willThrow(\Exception::class);
        $logger->warning('An error occurred trying to publish paused job execution id : 1')->shouldBeCalled();

        $this->publish();
    }
}
