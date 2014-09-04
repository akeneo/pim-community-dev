<?php

namespace spec\Pim\Bundle\NotificationBundle\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Entity\Notification;

class UserNotificationSpec extends ObjectBehavior
{
    function let(Notification $notification, User $user)
    {
        $this->beConstructedWith($notification, $user);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Entity\UserNotification');
    }

    function it_can_be_marked_as_viewed()
    {
        $this->isViewed()->shouldReturn(false);
        $this->setViewed(true)->shouldReturn($this);
        $this->isViewed()->shouldReturn(true);
    }

    function it_has_a_notification_event(Notification $notification2, $notification)
    {
        $this->getNotification()->shouldReturn($notification);
        $this->setNotification($notification2)->shouldReturn($this);
        $this->getNotification()->shouldReturn($notification2);
    }

    function it_has_a_user(User $user2, $user)
    {
        $this->getUser()->shouldReturn($user);
        $this->setUser($user2)->shouldReturn($this);
        $this->getUser()->shouldReturn($user2);
    }
}
