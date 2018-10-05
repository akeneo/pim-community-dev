<?php

namespace Specification\Akeneo\Platform\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;

class RegistrySpec extends ObjectBehavior
{
    function it_registers_and_exposes_widgets(WidgetInterface $widget)
    {
        $widget->getAlias()->willReturn('completeness');
        $this->add($widget, 1);
        $this->get('completeness')->shouldReturn($widget);
        $this->getAll()->shouldReturn([1 => $widget]);
    }

    function it_does_not_expose_unknown_widgets()
    {
        $this->get('completeness')->shouldReturn(null);
    }

    function it_registers_widgets_in_the_given_order(
        WidgetInterface $completeness,
        WidgetInterface $shortcut,
        WidgetInterface $manager
    ) {
        $this->add($completeness, 10);
        $this->add($shortcut, 30);
        $this->add($manager, 20);

        $this->getAll()->shouldReturn(
            [
                10 => $completeness,
                20 => $manager,
                30 => $shortcut
            ]
        );
    }

    function it_can_handle_duplicate_priority(WidgetInterface $completeness, WidgetInterface $shortcut)
    {
        $this->add($completeness, 2);
        $this->add($shortcut, 2);

        $this->getAll()->shouldReturn(
            [
                2 => $completeness,
                3 => $shortcut
            ]
        );
    }
}
