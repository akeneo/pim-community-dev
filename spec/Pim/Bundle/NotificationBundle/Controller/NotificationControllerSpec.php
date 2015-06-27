<?php

namespace spec\Pim\Bundle\NotificationBundle\Controller;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManagerInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationControllerSpec extends ObjectBehavior
{
    function let(DelegatingEngine $templating, NotificationManagerInterface $manager, UserContext $context)
    {
        $this->beConstructedWith($templating, $manager, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Controller\NotificationController');
    }

    function it_lists_user_notifications_linked_to_the_current_user(
        UserInterface $user,
        UserNotificationInterface $userNotification,
        Request $request,
        $manager,
        $context,
        $templating
    ) {
        $context->getUser()->willReturn($user);
        $manager->getUserNotifications($user, Argument::cetera())->willReturn([$userNotification]);

        $templating->renderResponse(
            'PimNotificationBundle:Notification:list.json.twig',
            [
                'userNotifications' => [$userNotification]
            ],
            Argument::type('Symfony\Component\HttpFoundation\JsonResponse')
        )->shouldBeCalled();

        $this->listAction($request);
    }

    function it_counts_unread_user_notifications_for_the_current_user(UserInterface $user, $manager, $context)
    {
        $context->getUser()->willReturn($user);
        $manager->countUnreadForUser($user)->willReturn(3);

        $response = $this->countUnreadAction($user);
        $response->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\JsonResponse');
        $response->getContent()->shouldReturn('3');
    }

    function it_marks_a_notification_as_viewed_for_a_user(UserInterface $user, $manager, $context)
    {
        $notifsToMark = '3';
        $context->getUser()->willReturn($user);
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_marks_notifications_as_viewed_for_the_current_user(UserInterface $user, $manager, $context)
    {
        $notifsToMark = '3';
        $context->getUser()->shouldBeCalled()->willReturn($user);
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
