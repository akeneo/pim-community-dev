<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;

class RegistrySpec extends ObjectBehavior
{
    function it_registers_and_exposes_widgets(WidgetInterface $widget)
    {
        $widget->getAlias()->willReturn('foo');
        $this->add($widget, 1);
        $this->get('foo')->shouldReturn($widget);
        $this->getAll()->shouldReturn([1 => $widget]);
    }

    function it_does_not_expose_unknown_widgets()
    {
        $this->get('bar')->shouldReturn(null);
    }

    function it_registers_widgets_in_the_given_order(WidgetInterface $foo, WidgetInterface $bar)
    {
        $this->add($foo, 1);
        $this->add($bar, 3);

        $this->getAll()->shouldReturn(
            [
                1 => $foo,
                3 => $bar
            ]
        );
    }

    function it_can_handle_duplicate_priority(WidgetInterface $foo, WidgetInterface $bar)
    {
        $this->add($foo, 2);
        $this->add($bar, 2);

        $this->getAll()->shouldReturn(
            [
                2 => $foo,
                3 => $bar
            ]
        );
    }
}
