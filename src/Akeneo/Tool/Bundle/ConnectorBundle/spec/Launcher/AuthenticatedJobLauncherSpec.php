<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\Launcher;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedJobLauncherSpec extends ObjectBehavior
{
    function let(JobLauncherInterface $jobLauncher)
    {
        $this->beConstructedWith($jobLauncher);
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType(JobLauncherInterface::class);
    }

    function it_should_force_authentication_in_configuration(
        $jobLauncher,
        JobInstance $jobInstance,
        UserInterface $user
    ) {
        $jobLauncher->launch($jobInstance, $user, ['filePath' => '/tmp', 'is_user_authenticated' => true]);
        $this->launch($jobInstance, $user, ['filePath' => '/tmp']);
    }
}
