<?php

namespace spec\Pim\Bundle\NotificationBundle\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Entity\Notification;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;

class NotificationControllerSpec extends ObjectBehavior
{
    function let(NotificationManager $manager)
    {
        $this->beConstructedWith($manager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Controller\NotificationController');
    }

    function it_lists_notifications_linked_to_a_user(
        User $user,
        Notification $notification,
        $manager
    ) {
        $manager->getNotifications($user)->shouldBeCalled()->willReturn([$notification]);
        $this->listAction($user)->shouldReturn(['notifications' => [$notification]]);
    }

    function it_marks_a_notification_as_viewed_for_a_user($manager)
    {
        $user = '1';
        $notifsToMark = '3';
        $manager->markNotificationsAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($user, $notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_marks_notifications_as_viewed_for_a_user($manager)
    {
        $user = '1';
        $notifsToMark = '3';
        $manager->markNotificationsAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($user, $notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
