<?php

namespace spec\Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SimpleJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobParametersValidator $jobParametersValidator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $jobRegistry, $jobParametersValidator, $eventDispatcher, '/', 'prod', '/logs');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType(JobLauncherInterface::class);
    }

    function it_launches_a_job_by_pushing_it_to_the_queue(
        $jobRegistry,
        $jobParametersFactory,
        $jobParametersValidator,
        $jobRepository,
        JobInstance $jobInstance,
        UserInterface $user,
        JobExecution $jobExecution,
        Job $job,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList,
        $eventDispatcher
    ) {
        $jobInstance->getJobName()->willReturn('job_instance_name');
        $jobInstance->getCode()->willReturn('job_instance_code');
        $jobInstance->getRawParameters()->willReturn(['foo' => 'bar']);
        $user->getUsername()->willReturn('julia');
        $jobExecution->getId()->willReturn(1);
        $constraintViolationList->count()->willReturn(0);

        $jobRegistry->get('job_instance_name')->willReturn($job);
        $jobParametersFactory->create($job, ['foo' => 'bar', 'baz' => 'foz'])->willReturn($jobParameters);
        $jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution'])->willReturn($constraintViolationList);
        $jobRepository->createJobExecution($jobInstance, $jobParameters)->willReturn($jobExecution);
        $jobExecution->setUser('julia')->shouldBeCalled();
        $jobRepository->updateJobExecution($jobExecution)->shouldBeCalled();

        $eventDispatcher->dispatch(EventInterface::JOB_EXECUTION_CREATED, Argument::type(JobExecutionEvent::class))->shouldBeCalled();

        $this->launch($jobInstance, $user, ['baz' => 'foz'])->shouldReturn($jobExecution);
    }

    function it_throws_an_exception_if_job_parameters_are_invalid(
        $jobRegistry,
        $jobParametersFactory,
        $jobParametersValidator,
        JobInstance $jobInstance,
        UserInterface $user,
        JobExecution $jobExecution,
        Job $job,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList,
        ConstraintViolation $constraintViolation,
        $eventDispatcher
    ) {
        $jobInstance->getJobName()->willReturn('job_instance_name');
        $jobInstance->getCode()->willReturn('job_instance_code');
        $jobInstance->getRawParameters()->willReturn(['foo' => 'bar']);
        $user->getUsername()->willReturn('julia');
        $jobExecution->getId()->willReturn(1);
        $constraintViolationList->count()->willReturn(1);

        $constraintViolationList->rewind()->shouldBeCalled();
        $constraintViolationList->valid()->willReturn(true, false);
        $constraintViolationList->next()->shouldBeCalled();
        $constraintViolationList->current()->willReturn($constraintViolation);

        $constraintViolation->__toString()->willReturn('error');

        $jobRegistry->get('job_instance_name')->willReturn($job);
        $jobParametersFactory->create($job, ['foo' => 'bar'])->willReturn($jobParameters);
        $jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution'])->willReturn($constraintViolationList);

        $eventDispatcher->dispatch(EventInterface::JOB_EXECUTION_CREATED, Argument::type(JobExecutionEvent::class))->shouldNotBeCalled();

        $this
            ->shouldThrow(new \RuntimeException('Job instance "job_instance_code" running the job "" with parameters "" is invalid because of "' . PHP_EOL .'  - error"'))
            ->during('launch', [$jobInstance, $user, []]);
    }
}
