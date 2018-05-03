<?php

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\Hydrator;

use Akeneo\Tool\Bundle\BatchQueueBundle\Hydrator\JobExecutionMessageHydrator;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class JobExecutionMessageHydratorSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $entityManager,
        Connection $connection,
        MySqlPlatform $platform
    ) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->getDatabasePlatform()->willReturn($platform);
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobExecutionMessageHydrator::class);
    }

    function it_hydrates_a_job_execution_message(JobExecutionMessage $jobExecutionMessage) {
        $row = [
            'id' => '1',
            'job_execution_id' => '2',
            'options' => '{"env": "test"}',
            'create_time' => '2017-09-19 13:30:00',
            'updated_time' => '2017-09-19 13:30:15',
            'consumer' =>  'consumer_name',
        ];
        $createdTime = new \DateTime('2017-09-19 13:30:00');
        $updatedTime = new \DateTime('2017-09-19 13:30:15');

        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessageFromDatabase(1, 2, 'consumer_name', $createdTime, $updatedTime, ['env' => 'test']);

        $this->hydrate($row)->shouldBeLike($jobExecutionMessage);
    }

    function it_throws_an_exception_if_a_property_is_missing() {
        $row = [
            'id' => '1',
            'job_execution_id' => '2',
            'options' => '{"env": "test"}',
            'create_time' => '2017-09-19 13:30:00',
            'updated_time' => null,
        ];

        $this
            ->shouldThrow(new MissingOptionsException('The required option "consumer" is missing.'))
            ->during('hydrate', [$row]);
    }
}
