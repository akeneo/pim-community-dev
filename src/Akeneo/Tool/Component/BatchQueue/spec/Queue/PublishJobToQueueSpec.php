<?php

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandler;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PublishJobToQueueSpec extends ObjectBehavior
{
    public function let(
        DoctrineJobRepository $jobRepository,
        ValidatorInterface $validator,
        JobExecutionQueueInterface $jobExecutionQueue,
        JobExecutionMessageFactory $jobExecutionMessageFactory,
        EventDispatcherInterface $eventDispatcher,
        BatchLogHandler $batchLogHandler,
        CreateJobExecutionHandler $createJobExecutionHandler,
    ): void {
        $this->beConstructedWith(
            'prod',
            $jobRepository,
            $validator,
            $jobExecutionQueue,
            $jobExecutionMessageFactory,
            $eventDispatcher,
            $batchLogHandler,
            $createJobExecutionHandler,
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PublishJobToQueue::class);
    }

    public function it_publishes_a_job_to_the_execution_queue(
        DoctrineJobRepository $jobRepository,
        JobExecutionQueueInterface $jobExecutionQueue,
        JobExecutionMessageFactory $jobExecutionMessageFactory,
        EventDispatcherInterface $eventDispatcher,
        BatchLogHandler $batchLogHandler,
        CreateJobExecutionHandler $createJobExecutionHandler,
        EntityManagerInterface $entityManager,
        ObjectRepository $jobInstanceRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
    ): void {
        $batchCode = 'job-code';
        $config = [];

        $jobRepository->getJobManager()->willReturn($entityManager);
        $entityManager->getRepository(JobInstance::class)->willReturn($jobInstanceRepository);

        $jobInstanceRepository->findOneBy(['code' => $batchCode])->willReturn($jobInstance);

        $createJobExecutionHandler->createFromJobInstance($jobInstance, $config, null)->willReturn($jobExecution);

        $jobExecution->getId()->willReturn(42);
        $batchLogHandler->setSubDirectory('42')->shouldBeCalled();

        $jobExecutionMessage = UiJobExecutionMessage::createJobExecutionMessage(42, []);
        $jobExecutionMessageFactory->buildFromJobInstance($jobInstance, 42, ['env' => 'prod'])
            ->willReturn($jobExecutionMessage);

        $jobExecutionQueue->publish($jobExecutionMessage)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(JobExecutionEvent::class), EventInterface::JOB_EXECUTION_CREATED)->shouldBeCalled();

        $this->publish($batchCode, $config)->shouldReturn($jobExecution);
    }
}
