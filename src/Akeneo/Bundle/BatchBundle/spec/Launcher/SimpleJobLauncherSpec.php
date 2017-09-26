<?php

namespace spec\Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
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
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SimpleJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobParametersValidator $jobParametersValidator
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $jobRegistry, $jobParametersValidator, '/', 'prod', '/logs');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType(JobLauncherInterface::class);
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
