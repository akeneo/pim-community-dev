<?php

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\DatabaseJobExecutionQueue;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobQueueConsumerConfiguration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DatabaseJobExecutionQueueSpec extends ObjectBehavior
{
    function let(JobExecutionMessageRepository $jobExecutionMessageRepository)
    {
        $this->beConstructedWith($jobExecutionMessageRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DatabaseJobExecutionQueue::class);
    }

    function it_publishes_a_job_execution_message(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->createJobExecutionMessage($jobExecutionMessage)->shouldBeCalled();
        $this->publish($jobExecutionMessage);
    }

    function it_consumes_a_job_execution_message_without_filter(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(Argument::any())->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableNotBlacklistedJobExecutionMessageFilteredByCodes(Argument::any())->shouldNotBeCalled();
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);
        $configuration = new JobQueueConsumerConfiguration();

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name', $configuration)->shouldReturn($jobExecutionMessage);
    }

    function it_consumes_a_job_execution_message_with_whitelist_filter(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(['csv_export_product'])->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->getAvailableNotBlacklistedJobExecutionMessageFilteredByCodes(['csv_export_product'])->shouldNotBeCalled();
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);
        $configuration = (new JobQueueConsumerConfiguration())
            ->setWhitelistedJobInstanceCodes(['csv_export_product']);

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name', $configuration)->shouldReturn($jobExecutionMessage);
    }

    function it_consumes_a_job_execution_message_with_blacklist_filter(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(['csv_export_product'])->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableNotBlacklistedJobExecutionMessageFilteredByCodes(['csv_export_product'])->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);
        $configuration = (new JobQueueConsumerConfiguration())
            ->setBlacklistedJobInstanceCodes(['csv_export_product']);

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name', $configuration)->shouldReturn($jobExecutionMessage);
    }
}
