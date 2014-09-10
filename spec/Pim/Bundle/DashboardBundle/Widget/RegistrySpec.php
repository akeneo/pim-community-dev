<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;

class RegistrySpec extends ObjectBehavior
{
    function it_registers_and_exposes_widget(WidgetInterface $widget)
    {
        $this->add('foo', $widget);
        $this->get('foo')->shouldReturn($widget);
    }

    function it_does_not_expose_unknown_widget()
    {
        $this->get('foo')->shouldReturn(null);
    }
}
