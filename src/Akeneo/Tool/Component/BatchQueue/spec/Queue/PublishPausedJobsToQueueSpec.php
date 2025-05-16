<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\GetPausedJobExecutionIdsInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PausedJobExecutionMessage;
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
        $getPausedJobExecutionIds->all()->willReturn([1, 9]);
        $jobExecutionQueue->publish(Argument::that(
            static fn (PausedJobExecutionMessage $jobMessage) => 1 === $jobMessage->getJobExecutionId()
        ))->shouldBeCalled()->willThrow(\Exception::class);
        $logger->error('An error occurred trying to publish paused job execution', [
            'job_execution_id' => 1,
            'error_message' => '',
        ])->shouldBeCalled();
        $jobExecutionQueue->publish(Argument::that(
            static fn (PausedJobExecutionMessage $jobMessage) => 9 === $jobMessage->getJobExecutionId()
        ))->shouldBeCalled();
        $logger->error('An error occurred trying to publish paused job execution', [
            'job_execution_id' => 9,
            'error_message' => '',
        ])->shouldNotBeCalled();

        $this->publish();
    }
}
