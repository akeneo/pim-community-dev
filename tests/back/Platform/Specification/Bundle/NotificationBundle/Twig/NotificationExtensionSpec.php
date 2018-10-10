<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle\Twig;

use Akeneo\Platform\Bundle\NotificationBundle\Twig\NotificationExtension;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationExtensionSpec extends ObjectBehavior
{
    function let(UserNotificationRepositoryInterface $userNotifRepository, UserContext $context)
    {
        $this->beConstructedWith($userNotifRepository, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotificationExtension::class);
    }

    function it_provides_the_unread_notification_count($context, $userNotifRepository, UserInterface $user)
    {
        $context->getUser()->willReturn($user);
        $userNotifRepository->countUnreadForUser($user)->willReturn(3);

        $this->countNotifications()->shouldReturn(3);
    }

    function it_returns_zero_if_no_user_is_present_in_the_context($userNotifRepository)
    {
        $userNotifRepository->countUnreadForUser(Argument::cetera())->shouldNotBeCalled();

        $this->countNotifications()->shouldReturn(0);
    }
}
