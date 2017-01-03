<?php

namespace spec\Pim\Bundle\NotificationBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationControllerSpec extends ObjectBehavior
{
    function let(
        DelegatingEngine $templating,
        UserContext $context,
        UserNotificationRepositoryInterface $userNotifRepository,
        RemoverInterface $userNotifRemover
    ) {
        $this->beConstructedWith($templating, $context, $userNotifRepository, $userNotifRemover);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Controller\NotificationController');
    }

    function it_lists_user_notifications_linked_to_the_current_user(
        $userNotifRepository,
        $context,
        $templating,
        UserInterface $user,
        UserNotificationInterface $userNotification,
        Request $request
    ) {
        $context->getUser()->willReturn($user);
        $userNotifRepository->findBy(['user' => $user], ['id' => 'DESC'], 10, null)
            ->willReturn([$userNotification]);

        $templating->renderResponse(
            'PimNotificationBundle:Notification:list.json.twig',
            [
                'userNotifications' => [$userNotification]
            ],
            Argument::type('Symfony\Component\HttpFoundation\JsonResponse')
        )->shouldBeCalled();

        $this->listAction($request);
    }

    function it_counts_unread_user_notifications_for_the_current_user(
        $userNotifRepository,
        $context,
        UserInterface $user
    ) {
        $context->getUser()->willReturn($user);
        $userNotifRepository->countUnreadForUser($user)->willReturn(3);

        $response = $this->countUnreadAction($user);
        $response->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\JsonResponse');
        $response->getContent()->shouldReturn('3');
    }

    function it_marks_a_notification_as_viewed_for_a_user($userNotifRepository, $context, UserInterface $user)
    {
        $notifsToMark = '3';
        $context->getUser()->willReturn($user);
        $userNotifRepository->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_marks_notifications_as_viewed_for_the_current_user($userNotifRepository, $context, UserInterface $user)
    {
        $notifsToMark = '3';
        $context->getUser()->shouldBeCalled()->willReturn($user);
        $userNotifRepository->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
