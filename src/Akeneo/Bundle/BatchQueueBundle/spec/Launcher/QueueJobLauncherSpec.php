<?php

namespace spec\Akeneo\Bundle\BatchQueueBundle\Launcher;

use Akeneo\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\User;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class QueueJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobParametersValidator $jobParametersValidator,
        JobExecutionQueueInterface $queue
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $jobRegistry, $jobParametersValidator, $queue, 'test');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType(QueueJobLauncher::class);
    }

    function it_launches_a_job_by_pushing_it_to_the_queue(
        $jobRegistry,
        $jobParametersFactory,
        $jobParametersValidator,
        $jobRepository,
        $queue,
        JobInstance $jobInstance,
        UserInterface $user,
        JobExecution $jobExecution,
        Job $job,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $jobInstance->getJobName()->willReturn('job_instance_name');
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

        $queue->publish(Argument::type(JobExecutionMessage::class))->shouldBeCalled();

        $this->launch($jobInstance, $user, ['baz' => 'foz'])->shouldReturn($jobExecution);
    }

    function it_launches_a_job_by_pushing_it_to_the_queue_with_email(
        $jobRegistry,
        $jobParametersFactory,
        $jobParametersValidator,
        $jobRepository,
        $queue,
        JobInstance $jobInstance,
        User $user,
        JobExecutionMessage $jobExecutionMessage,
        JobExecution $jobExecution,
        Job $job,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $jobInstance->getJobName()->willReturn('job_instance_name');
        $jobInstance->getRawParameters()->willReturn(['foo' => 'bar']);
        $user->getUsername()->willReturn('julia');
        $jobExecution->getId()->willReturn(1);
        $constraintViolationList->count()->willReturn(0);

        $user->getEmail()->willReturn('julia@akeneo.com');

        $jobRegistry->get('job_instance_name')->willReturn($job);
        $jobParametersFactory->create($job, ['foo' => 'bar', 'baz' => 'foz'])->willReturn($jobParameters);
        $jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution'])->willReturn($constraintViolationList);
        $jobRepository->createJobExecution($jobInstance, $jobParameters)->willReturn($jobExecution);
        $jobExecution->setUser('julia')->shouldBeCalled();
        $jobRepository->updateJobExecution($jobExecution)->shouldBeCalled();

        $queue->publish(Argument::type(JobExecutionMessage::class))->shouldBeCalled();

        $this->launch($jobInstance, $user, ['baz' => 'foz', 'send_email' => true])->shouldReturn($jobExecution);
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
        ConstraintViolation $constraintViolation
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

        $this
            ->shouldThrow(new \RuntimeException('Job instance "job_instance_code" running the job "" with parameters "" is invalid because of "' . PHP_EOL .'  - error"'))
            ->during('launch', [$jobInstance, $user, []]);
    }
}
