<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle\Factory;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotification;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(UserNotification::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserNotificationFactory::class);
    }

    function it_creates_user_notifications(NotificationInterface $notification, UserInterface $user)
    {
        $userNotification = $this->createUserNotification($notification, $user);

        $userNotification->shouldHaveType(UserNotification::class);
        $userNotification->getNotification()->shouldReturn($notification);
        $userNotification->getUser()->shouldReturn($user);
    }
}
