<?php

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
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
    function let(
        DoctrineJobRepository $jobRepository,
        ValidatorInterface $validator,
        JobRegistry $jobRegistry,
        JobParametersFactory $jobParametersFactory,
        EntityManagerInterface $entityManager,
        JobParametersValidator $jobParametersValidator,
        JobExecutionQueueInterface $jobExecutionQueue,
        JobExecutionMessageFactory $jobExecutionMessageFactory,
        EventDispatcherInterface $eventDispatcher,
        BatchLogHandler $batchLogHandler
    ) {
        $this->beConstructedWith(
            JobInstance::class,
            'prod',
            $jobRepository,
            $validator,
            $jobRegistry,
            $jobParametersFactory,
            $entityManager,
            $jobParametersValidator,
            $jobExecutionQueue,
            $jobExecutionMessageFactory,
            $eventDispatcher,
            $batchLogHandler
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PublishJobToQueue::class);
    }

    function it_publishes_a_job_to_the_execution_queue(
        DoctrineJobRepository $jobRepository,
        JobRegistry $jobRegistry,
        JobParametersFactory $jobParametersFactory,
        EntityManagerInterface $entityManager,
        JobParametersValidator $jobParametersValidator,
        JobExecutionQueueInterface $jobExecutionQueue,
        EventDispatcherInterface $eventDispatcher,
        BatchLogHandler $batchLogHandler,
        ObjectRepository $jobInstanceRepository,
        JobInstance $jobInstance,
        Job $job,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobExecutionMessageFactory $jobExecutionMessageFactory
    ) {
        $jobInstance->getJobName()->willReturn('job-code');
        $jobInstance->getRawParameters()->willReturn([]);

        $jobInstanceRepository->findOneBy(['code' => 'job-code'])->willReturn($jobInstance);
        $entityManager->getRepository(JobInstance::class)->willReturn($jobInstanceRepository);
        $jobRepository->getJobManager()->willReturn($entityManager);

        $jobRegistry->get('job-code')->willReturn($job);
        $jobParametersFactory->create($job, [])->willReturn($jobParameters);

        $entityManager->merge($jobInstance)->shouldBeCalled();
        $jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution'])
                               ->shouldBeCalled()->willReturn(new ConstraintViolationList([]));
        $entityManager->clear(get_class($jobInstance->getWrappedObject()))->shouldBeCalled();

        $jobExecution->getId()->willReturn(42);
        $jobRepository->createJobExecution($jobInstance, $jobParameters)->shouldBeCalled()->willReturn($jobExecution);

        $jobExecutionMessage = UiJobExecutionMessage::createJobExecutionMessage(42, []);
        $jobExecutionMessageFactory->buildFromJobInstance($jobInstance, 42, ['env' => 'prod'])
            ->willReturn($jobExecutionMessage);

        $batchLogHandler->setSubDirectory('42')->shouldBeCalled();
        $jobRepository->updateJobExecution($jobExecution)->shouldBeCalled();

        $jobExecutionQueue->publish($jobExecutionMessage)->shouldBeCalled();
        $eventDispatcher->dispatch(EventInterface::JOB_EXECUTION_CREATED, Argument::type(JobExecutionEvent::class))->shouldBeCalled();

        $this->publish('job-code', []);
    }
}
