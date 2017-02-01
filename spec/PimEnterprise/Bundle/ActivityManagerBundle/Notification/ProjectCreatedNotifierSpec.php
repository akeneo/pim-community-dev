<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotifier;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectCreatedNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectCreatedNotificationFactory $projectCreatedNotificationFactory,
        NotifierInterface $notifier
    ) {
        $this->beConstructedWith($projectCreatedNotificationFactory, $notifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCreatedNotifier::class);
    }

    function it_is_a_notifier()
    {
        $this->shouldImplement(ProjectNotifierInterface::class);
    }

    function it_does_not_notify_owner_that_a_project_is_created(
        $projectCreatedNotificationFactory,
        $notifier,
        NotificationInterface $notification,
        UserInterface $owner,
        ProjectInterface $project
    ) {
        $owner->getUsername()->willReturn('boby');
        $project->getOwner()->willReturn($owner);

        $projectCreatedNotificationFactory
            ->create($project, $owner)
            ->shouldNotBeCalled();

        $notifier->notify($notification, [$owner])->shouldNotBeCalled();

        $this->notifyUser($owner, $project);
    }

    function it_notifies_contributor_that_a_project_is_created(
        $projectCreatedNotificationFactory,
        $notifier,
        NotificationInterface $notification,
        UserInterface $contributor,
        UserInterface $owner,
        ProjectInterface $project
    ) {
        $contributor->getUsername()->willReturn('boby');
        $owner->getUsername()->willReturn('claude');
        $project->getOwner()->willReturn($owner);

        $projectCreatedNotificationFactory
            ->create($project, $contributor)
            ->willReturn($notification);

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project);
    }
}
