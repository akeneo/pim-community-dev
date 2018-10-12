<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle\Controller;

use Akeneo\Platform\Bundle\NotificationBundle\Controller\NotificationController;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $this->shouldHaveType(NotificationController::class);
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
        $context->getUserTimezone()->willReturn('Europe/Paris');

        $templating->renderResponse(
            'PimNotificationBundle:Notification:list.json.twig',
            [
                'userNotifications' => [$userNotification],
                'userTimezone' => 'Europe/Paris',
            ],
            Argument::type(JsonResponse::class)
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
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn('3');
    }

    function it_marks_a_notification_as_viewed_for_a_user($userNotifRepository, $context, UserInterface $user)
    {
        $notifsToMark = '3';
        $context->getUser()->willReturn($user);
        $userNotifRepository->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf(Response::class);
    }

    function it_marks_notifications_as_viewed_for_the_current_user($userNotifRepository, $context, UserInterface $user)
    {
        $notifsToMark = '3';
        $context->getUser()->shouldBeCalled()->willReturn($user);
        $userNotifRepository->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf(Response::class);
    }
}
