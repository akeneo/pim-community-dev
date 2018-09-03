<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Manager;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JobExecutionManagerSpec extends ObjectBehavior
{
    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_checks_a_job_execution_is_running(
        JobExecution $jobExecution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $status->getValue()->willReturn(BatchStatus::STARTING);
        $exitStatus->getExitCode()->willReturn(ExitStatus::EXECUTING);

        $this->checkRunningStatus($jobExecution)->shouldReturn(true);
    }

    function it_checks_a_job_execution_is_not_running(
        JobExecution $jobExecution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $status->getValue()->willReturn(BatchStatus::STARTING);
        $exitStatus->getExitCode()->willReturn(ExitStatus::STOPPED);

        $this->checkRunningStatus($jobExecution)->shouldReturn(true);
    }

    function it_marks_a_job_execution_as_failed($entityManager, JobExecution $jobExecution)
    {
        $jobExecution->setStatus(Argument::any())->shouldBeCalled();
        $jobExecution->setExitStatus(Argument::any())->shouldBeCalled();
        $jobExecution->setEndTime(Argument::any())->shouldBeCalled();
        $jobExecution->addFailureException(Argument::any())->shouldBeCalled();

        $entityManager->persist($jobExecution)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->markAsFailed($jobExecution);
    }
}
