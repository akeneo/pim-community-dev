<?php

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\GetPausedJobExecutionIdsInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishPausedJobsToQueue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PublishPausedJobsToQueueSpec extends ObjectBehavior
{
    public function let(
        JobExecutionQueueInterface $jobExecutionQueue,
        GetPausedJobExecutionIdsInterface $getPausedJobExecutionIds,
    ): void {
        $this->beConstructedWith(
            $jobExecutionQueue,
            $getPausedJobExecutionIds
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

        $this->publishPausedJobs();
    }
}
