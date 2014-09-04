<?php

namespace spec\Pim\Bundle\NotificationBundle\Controller;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Symfony\Component\HttpFoundation\Request;

class NotificationControllerSpec extends ObjectBehavior
{
    function let(UserNotificationManager $manager, UserContext $context)
    {
        $this->beConstructedWith($manager, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Controller\NotificationController');
    }

    function it_lists_user_notifications_linked_to_a_user(
        User $user,
        UserNotification $userNotification,
        Request $request,
        $manager
    ) {
        $manager->getUserNotifications($user, Argument::cetera())->shouldBeCalled()->willReturn([$userNotification]);
        $this->listAction($user, $request)->shouldReturn(['userNotifications' => [$userNotification]]);
    }

    function it_marks_a_notification_as_viewed_for_a_user(User $user, $manager, $context)
    {
        $notifsToMark = '3';
        $context->getUser()->shouldBeCalled()->willReturn($user);
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_marks_notifications_as_viewed_for_a_user(User $user, $manager, $context)
    {
        $notifsToMark = '3';
        $context->getUser()->shouldBeCalled()->willReturn($user);
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
