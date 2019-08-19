<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Factory;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class MassEditNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['mass_edit'], Notification::class);
    }

    function it_supports_type()
    {
        $this->supports('mass_edit')->shouldReturn(true);
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

        $this->create($jobExecution)->shouldReturnAnInstanceOf(Notification::class);
    }

    function it_throws_an_exception_if_param_is_not_an_exception()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('Expects a Akeneo\Tool\Component\Batch\Model\JobExecution, "stdClass" provided')
        )->during('create', [new \stdClass()]);
    }
}
