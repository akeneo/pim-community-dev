<?php

namespace spec\Akeneo\Bundle\BatchQueueBundle\Queue;

use Akeneo\Bundle\BatchQueueBundle\Hydrator\JobExecutionMessageHydrator;
use Akeneo\Bundle\BatchQueueBundle\Queue\DatabaseJobExecutionQueue;
use Akeneo\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

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
        $connection,
        JobExecutionMessage $jobExecutionMessage,
        Statement $stmt
    ) {
        $jobExecutionMessageRepository->getAvailableJobExecutionMessage()->willReturn($jobExecutionMessage);
        $jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage)->shouldBeCalled();

        $jobExecutionMessage->getJobExecutionId()->willReturn(1);
        $jobExecutionMessage->consumedBy('consumer_name')->shouldBeCalled();

        $connection->prepare(Argument::type('string'))->willReturn($stmt);
        $stmt->bindValue('lock', DatabaseJobExecutionQueue::LOCK_PREFIX . '1')->shouldBeCalled();
        $stmt->execute()->shouldBeCalled();

        $stmt->fetch()->willReturn(['1']);

        $this->consume('consumer_name');
    }
}
