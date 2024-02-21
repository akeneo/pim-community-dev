<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle\Entity;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotification;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserNotificationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UserNotification::class);
    }

    function it_can_be_marked_as_viewed()
    {
        $this->isViewed()->shouldReturn(false);
        $this->setViewed(true)->shouldReturn($this);
        $this->isViewed()->shouldReturn(true);
    }

    function it_has_a_notification_event(NotificationInterface $notification)
    {
        $this->getNotification()->shouldReturn(null);
        $this->setNotification($notification)->shouldReturn($this);
        $this->getNotification()->shouldReturn($notification);
    }

    function it_has_a_user(UserInterface $user)
    {
        $this->getUser()->shouldReturn(null);
        $this->setUser($user)->shouldReturn($this);
        $this->getUser()->shouldReturn($user);
    }
}
