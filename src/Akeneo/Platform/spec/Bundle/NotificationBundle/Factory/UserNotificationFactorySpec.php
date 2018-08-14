<?php

namespace spec\Akeneo\Platform\Bundle\NotificationBundle\Factory;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Platform\Bundle\NotificationBundle\Factory\UserNotificationFactory');
    }

    function it_creates_user_notifications(NotificationInterface $notification, UserInterface $user)
    {
        $userNotification = $this->createUserNotification($notification, $user);

        $userNotification->shouldHaveType('Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotification');
        $userNotification->getNotification()->shouldReturn($notification);
        $userNotification->getUser()->shouldReturn($user);
    }
}
