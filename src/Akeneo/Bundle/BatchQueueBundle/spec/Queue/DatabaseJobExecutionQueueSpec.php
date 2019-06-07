<?php

namespace spec\Akeneo\Bundle\BatchQueueBundle\Queue;

use Akeneo\Bundle\BatchQueueBundle\Queue\DatabaseJobExecutionQueue;
use Akeneo\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DatabaseJobExecutionQueueSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $entityManager,
        Connection $connection,
        JobExecutionMessageRepository $jobExecutionMessageRepository
    ) {
        $entityManager->getConnection()->willReturn($connection);
        $this->beConstructedWith($entityManager, $jobExecutionMessageRepository);
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

    function it_consumes_a_job_execution_message(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(Argument::any())->shouldNotBeCalled();
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);

        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $this->consume('consumer_name')->shouldReturn($jobExecutionMessage);
    }

    function it_filters_the_job_execution_to_consume(
        $jobExecutionMessageRepository,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->shouldNotBeCalled();
        $jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes(['csv_export_product'])->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->willReturn(true);
        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();
        $this->consume('consumer_name', ['csv_export_product'])->shouldReturn($jobExecutionMessage);
    }
}
