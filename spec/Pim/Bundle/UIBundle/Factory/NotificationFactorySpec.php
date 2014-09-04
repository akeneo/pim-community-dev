<?php

namespace spec\Pim\Bundle\UIBundle\Factory;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UIBundle\Entity\NotificationEvent;
use Prophecy\Argument;

class NotificationFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\UIBundle\Factory\NotificationFactory');
    }

    function it_creates_notification_events()
    {
        $options = [
            'messageParams' => ['foo' => 'bar'],
            'route' => 'index',
            'routeParams' => ['bar' => 'foo'],
            'context' => ['baz' => 'qux']
        ];

        $event = $this->createNotificationEvent('Some message', 'success', $options);

        $event->shouldHaveType('Pim\Bundle\UIBundle\Entity\NotificationEvent');
        $event->getMessage()->shouldReturn('Some message');
        $event->getMessageParams()->shouldReturn(['foo' => 'bar']);
        $event->getType()->shouldReturn('success');
        $event->getRoute()->shouldReturn('index');
        $event->getRouteParams()->shouldReturn(['bar' => 'foo']);
        $event->getCreated()->shouldReturnAnInstanceOf('\DateTime');
        $event->getContext()->shouldReturn(['baz' => 'qux']);
    }

    function it_creates_notifications(NotificationEvent $event, User $user)
    {
        $notification = $this->createNotification($event, $user);

        $notification->shouldHaveType('Pim\Bundle\UIBundle\Entity\Notification');
        $notification->getEvent()->shouldReturn($event);
        $notification->getUser()->shouldReturn($user);
    }
}
