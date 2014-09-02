<?php

namespace spec\Pim\Bundle\UIBundle\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UIBundle\Entity\NotificationEvent;

class NotificationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\UIBundle\Entity\Notification');
    }

    function it_can_be_marked_as_viewed()
    {
        $this->isViewed()->shouldReturn(false);
        $this->setViewed(true)->shouldReturn($this);
        $this->isViewed()->shouldReturn(true);
    }

    function it_has_a_notification_event(NotificationEvent $notificationEvent)
    {
        $this->getNotificationEvent()->shouldReturn(null);
        $this->setNotificationEvent($notificationEvent)->shouldReturn($this);
        $this->getNotificationEvent()->shouldReturn($notificationEvent);
    }

    function it_has_a_user(User $user)
    {
        $this->getUser()->shouldReturn(null);
        $this->setUser($user)->shouldReturn($this);
        $this->getUser()->shouldReturn($user);
    }
}
