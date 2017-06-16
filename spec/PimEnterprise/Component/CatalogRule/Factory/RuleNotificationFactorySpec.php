<?php

namespace spec\PimEnterprise\Component\CatalogRule\Factory;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class RuleNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['mass_edit_rule'], 'Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_supports_type()
    {
        $this->supports('mass_edit_rule')->shouldReturn(true);
        $this->supports('mass_edit')->shouldReturn(false);
    }

    function it_returns_factory(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        BatchStatus $batchStatus
    ) {
        $jobExecution->getId()->shouldBeCalled();
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getStatus()->willReturn($batchStatus);
        $batchStatus->isUnsuccessful()->willReturn(true);
        $jobInstance->getType()->willReturn('mass_edit_rule');
        $jobInstance->getLabel()->willReturn('Mass edit rule');

        $this->create($jobExecution)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_throws_an_exception_if_param_is_not_an_exception()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('Expects a Akeneo\Component\Batch\Model\JobExecution, "stdClass" provided')
        )->during('create', [new \stdClass()]);
    }
}
