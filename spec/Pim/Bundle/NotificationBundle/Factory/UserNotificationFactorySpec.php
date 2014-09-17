<?php

namespace spec\Pim\Bundle\NotificationBundle\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\NotificationBundle\Entity\Notification;
use Oro\Bundle\UserBundle\Entity\User;

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

    function it_creates_user_notifications(Notification $notification, User $user)
    {
        $userNotification = $this->createUserNotification($notification, $user);

        $userNotification->shouldHaveType('Pim\Bundle\NotificationBundle\Entity\UserNotification');
        $userNotification->getNotification()->shouldReturn($notification);
        $userNotification->getUser()->shouldReturn($user);
    }
}
