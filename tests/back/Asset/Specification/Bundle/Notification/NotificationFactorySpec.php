<?php

namespace Specification\Akeneo\Asset\Bundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class NotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['mass_upload'], NotificationInterface::class);
    }

    function it_supports_type()
    {
        $this->supports('mass_upload')->shouldReturn(true);
        $this->supports('mass_edit')->shouldReturn(false);
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
        $jobInstance->getType()->willReturn('mass_upload');
        $jobInstance->getLabel()->willReturn('Mass upload');

        $this->create($jobExecution)->shouldReturnAnInstanceOf(NotificationInterface::class);
    }

    function it_throws_an_exception_if_param_is_not_an_exception()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                sprintf('Expects a %s, "%s" provided', JobExecution::class, \stdClass::class))
        )->during('create', [new \stdClass()]);
    }
}
