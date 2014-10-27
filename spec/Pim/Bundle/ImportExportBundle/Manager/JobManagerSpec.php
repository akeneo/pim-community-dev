<?php

namespace spec\Pim\Bundle\ImportExportBundle\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\Job;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Prophecy\Argument;

class JobManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Manager\JobManager');
    }

    function let(EventDispatcherInterface $eventDispatcher, SmartManagerRegistry $managerRegistry)
    {
        $this->beConstructedWith($eventDispatcher, $managerRegistry, 'Akeneo\Bundle\BatchBundle\Entity\JobExecution');
    }

    function it_launchs_job(
        JobInstance $jobInstance,
        User $user,
        SmartManagerRegistry $managerRegistry,
        JobInstance $jobInstance,
        Job $job,
        ObjectManager $objectManager
    ) {
        $jobInstance->getJob()->shouldBeCalled()->willReturn($job);
        $jobInstance->getCode()->shouldBeCalled()->willReturn('code');
        $jobInstance->addJobExecution(Argument::any())->shouldBeCalled();
        $job->getConfiguration()->shouldBeCalled()->willReturn([]);
        $user->getEmail()->willReturn('test@example.com');
        $user->getUsername()->willReturn('john');
        $managerRegistry->getManagerForClass('Akeneo\Bundle\BatchBundle\Entity\JobExecution')->shouldBeCalled()->willReturn($objectManager);

        $objectManager->persist(Argument::any())->shouldBeCalled();
        $objectManager->flush(Argument::any())->shouldBeCalled();


        $this->launch($jobInstance, $user, 'test', 'test', true);
    }
}
