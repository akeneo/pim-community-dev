<?php

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\DatabaseJobExecutionQueue;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
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

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name')->shouldReturn($jobExecutionMessage);
    }

    function it_consumes_a_job_execution_message_with_whitelist_filter(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(['csv_export_product'])->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->getAvailableNotBlacklistedJobExecutionMessageFilteredByCodes(['csv_export_product'])->shouldNotBeCalled();
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name', ['csv_export_product'])->shouldReturn($jobExecutionMessage);
    }

    function it_consumes_a_job_execution_message_with_blacklist_filter(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(['csv_export_product'])->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableNotBlacklistedJobExecutionMessageFilteredByCodes(['csv_export_product'])->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name', [], ['csv_export_product'])->shouldReturn($jobExecutionMessage);
    }

    function it_throws_an_exception_when_trying_to_consume_a_job_execution_message_with_both_whitelist_and_blacklist_filters(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during('consume', ['consumer_name', ['csv_export_product'], ['csv_export_product']]);
    }
}
