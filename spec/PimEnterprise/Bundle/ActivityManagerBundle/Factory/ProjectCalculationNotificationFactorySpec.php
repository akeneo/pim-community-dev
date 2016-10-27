<?php

namespace spec\Akeneo\ActivityManager\Factory;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class ProjectCalculationNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['project_calculation'], 'Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_supports_type()
    {
        $this->supports('project_calculation')->shouldReturn(true);
        $this->supports('import')->shouldReturn(false);
    }

    function it_returns_factory(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        BatchStatus $batchStatus
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getStatus()->willReturn($batchStatus);
        $jobExecution->getId()->willReturn(1);
        $batchStatus->isUnsuccessful()->willReturn(true);
        $jobInstance->getType()->willReturn('import');
        $jobInstance->getLabel()->willReturn('Import');

        $this->create($jobExecution)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_throws_an_exception_if_param_is_not_an_exception()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('Expects a Akeneo\Component\Batch\Model\JobExecution, "stdClass" provided')
        )->during('create', [new \stdClass()]);
    }
}
