<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle\Entity;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use PhpSpec\ObjectBehavior;

class NotificationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Notification::class);
    }

    function it_can_have_a_route()
    {
        $this->getRoute()->shouldReturn(null);
        $this->setRoute('foo')->shouldReturn($this);
        $this->getRoute()->shouldReturn('foo');
    }

    function it_can_have_route_params()
    {
        $this->getRouteParams()->shouldReturn([]);
        $this->setRouteParams(['foo' => 'bar'])->shouldReturn($this);
        $this->getRouteParams()->shouldReturn(['foo' => 'bar']);
    }

    function it_has_a_message()
    {
        $this->setMessage('bar')->shouldReturn($this);
        $this->getMessage()->shouldReturn('bar');
    }

    function it_has_message_params()
    {
        $this->getMessageParams()->shouldReturn([]);
        $this->setMessageParams(['foo' => 'bar'])->shouldReturn($this);
        $this->getMessageParams()->shouldReturn(['foo' => 'bar']);
    }

    function it_has_a_creation_date()
    {
        $this->getCreated()->shouldReturnAnInstanceOf('\DateTime');
    }

    function it_has_a_type()
    {
        $this->setType('success')->shouldReturn($this);
        $this->getType()->shouldReturn('success');
    }

    function it_can_have_a_context()
    {
        $this->getContext()->shouldReturn([]);
        $this->setContext(['foo' => 'bar'])->shouldReturn($this);
        $this->getContext()->shouldReturn(['foo' => 'bar']);
    }
}
