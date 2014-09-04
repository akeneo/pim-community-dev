<?php

namespace spec\Pim\Bundle\NotificationBundle\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Symfony\Component\HttpFoundation\Request;

class NotificationControllerSpec extends ObjectBehavior
{
    function let(UserNotificationManager $manager)
    {
        $this->beConstructedWith($manager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Controller\NotificationController');
    }

    function it_lists_notifications_linked_to_a_user(
        User $user,
        UserNotification $notification,
        Request $request,
        $manager
    ) {
        $manager->getUserNotifications($user, Argument::cetera())->shouldBeCalled()->willReturn([$notification]);
        $this->listAction($user, $request)->shouldReturn(['notifications' => [$notification]]);
    }

    function it_marks_a_notification_as_viewed_for_a_user($manager)
    {
        $user = '1';
        $notifsToMark = '3';
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($user, $notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_marks_notifications_as_viewed_for_a_user($manager)
    {
        $user = '1';
        $notifsToMark = '3';
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($user, $notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
