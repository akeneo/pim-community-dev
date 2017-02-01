<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotifier;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectFinishedNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectFinishedNotificationFactory $projectFinishedNotificationFactory,
        NotifierInterface $notifier
    ) {
        $this->beConstructedWith($projectFinishedNotificationFactory, $notifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectFinishedNotifier::class);
    }

    function it_is_a_notifier()
    {
        $this->shouldImplement(ProjectNotifierInterface::class);
    }

    function it_notifies_owner_that_a_project_is_finished(
        $projectFinishedNotificationFactory,
        $notifier,
        NotificationInterface $notification,
        UserInterface $owner,
        ProjectInterface $project
    ) {
        $owner->getUsername()->willReturn('boby');
        $project->getOwner()->willReturn($owner);

        $projectFinishedNotificationFactory
            ->create($project, 'activity_manager.notification.project_finished.owner')
            ->willReturn($notification);

        $projectFinishedNotificationFactory
            ->create($project, 'activity_manager.notification.project_finished.contributor')
            ->shouldNotBeCalled();

        $notifier->notify($notification, [$owner])->shouldBeCalled();

        $this->notifyUser($owner, $project);
    }

    function it_notifies_contributor_that_a_project_is_finished(
        $projectFinishedNotificationFactory,
        $notifier,
        NotificationInterface $notification,
        UserInterface $contributor,
        UserInterface $owner,
        ProjectInterface $project
    ) {
        $contributor->getUsername()->willReturn('boby');
        $owner->getUsername()->willReturn('claude');
        $project->getOwner()->willReturn($owner);

        $projectFinishedNotificationFactory
            ->create($project, 'activity_manager.notification.project_finished.contributor')
            ->willReturn($notification);

        $projectFinishedNotificationFactory
            ->create($project, 'activity_manager.notification.project_finished.owner')
            ->shouldNotBeCalled();

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project);
    }
}
