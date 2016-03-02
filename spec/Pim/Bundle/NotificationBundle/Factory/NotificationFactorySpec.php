<?php

namespace spec\Pim\Bundle\NotificationBundle\Factory;

use PhpSpec\ObjectBehavior;

class NotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Factory\NotificationFactory');
    }

    function it_creates_notifications()
    {
        $options = [
            'messageParams' => ['foo' => 'bar'],
            'route' => 'index',
            'routeParams' => ['bar' => 'foo'],
            'context' => ['baz' => 'qux'],
            'comment' => 'blabla',
        ];

        $notification = $this->createNotification('Some message', 'success', $options);

        $notification->shouldHaveType('Pim\Bundle\NotificationBundle\Entity\Notification');
        $notification->getMessage()->shouldReturn('Some message');
        $notification->getMessageParams()->shouldReturn(['foo' => 'bar']);
        $notification->getType()->shouldReturn('success');
        $notification->getRoute()->shouldReturn('index');
        $notification->getRouteParams()->shouldReturn(['bar' => 'foo']);
        $notification->getCreated()->shouldReturnAnInstanceOf('\DateTime');
        $notification->getContext()->shouldReturn(['baz' => 'qux']);
        $notification->getComment()->shouldReturn('blabla');
    }

    function it_creates_notification_with_some_null_params()
    {
        $options = [];
        $notification = $this->createNotification('Some message', 'success', $options);

        $notification->shouldHaveType('Pim\Bundle\NotificationBundle\Entity\Notification');
        $notification->getMessage()->shouldReturn('Some message');
        $notification->getMessageParams()->shouldReturn([]);
        $notification->getType()->shouldReturn('success');
        $notification->getRoute()->shouldReturn(null);
        $notification->getRouteParams()->shouldReturn([]);
        $notification->getCreated()->shouldReturnAnInstanceOf('\DateTime');
        $notification->getContext()->shouldReturn([]);
        $notification->getComment()->shouldReturn(null);
    }
}
