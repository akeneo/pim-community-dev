<?php

namespace spec\Pim\Bundle\NotificationBundle\Controller;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Request;

class NotificationControllerSpec extends ObjectBehavior
{
    function let(DelegatingEngine $templating, UserNotificationManager $manager, UserContext $context)
    {
        $this->beConstructedWith($templating, $manager, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Controller\NotificationController');
    }

    function it_lists_user_notifications_linked_to_the_current_user(
        User $user,
        UserNotification $userNotification,
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

    function it_marks_a_notification_as_viewed_for_a_user(User $user, $manager, $context)
    {
        $notifsToMark = '3';
        $context->getUser()->willReturn($user);
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_marks_notifications_as_viewed_for_the_current_user(User $user, $manager, $context)
    {
        $notifsToMark = '3';
        $context->getUser()->shouldBeCalled()->willReturn($user);
        $manager->markAsViewed($user, $notifsToMark)->shouldBeCalled();

        $this
            ->markAsViewedAction($notifsToMark)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
