<?php

namespace spec\PimEnterprise\Component\ProductAsset\Factory;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class NotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['mass_upload'], 'Pim\Bundle\NotificationBundle\Entity\Notification');
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

        $this->create($jobExecution)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_throws_an_exception_if_param_is_not_an_exception()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('Expects a Akeneo\Component\Batch\Model\JobExecution, "stdClass" provided')
        )->during('create', [new \stdClass()]);
    }
}
