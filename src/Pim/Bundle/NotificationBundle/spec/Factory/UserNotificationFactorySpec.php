<?php

namespace spec\Pim\Bundle\NotificationBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\NotificationBundle\Entity\UserNotification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory');
    }

    function it_creates_user_notifications(NotificationInterface $notification, UserInterface $user)
    {
        $userNotification = $this->createUserNotification($notification, $user);

        $userNotification->shouldHaveType('Pim\Bundle\NotificationBundle\Entity\UserNotification');
        $userNotification->getNotification()->shouldReturn($notification);
        $userNotification->getUser()->shouldReturn($user);
    }
}
