<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditMassActionSpec extends ObjectBehavior
{
    function it_requires_the_route()
    {
        $params = [];
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->shouldThrow(
            new \LogicException('There is no option "route" for action "edit".')
        )->duringSetOptions($options);
    }

    function it_defines_default_values()
    {
        $params = ['route' => 'foo'];
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('edit');
        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('redirect');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn([]);
        $this->getOptions()->offsetGet('handler')->shouldReturn('mass_edit');
    }

    function it_overwrites_default_values()
    {
        $routeParams = ['foo' => 'bar'];
        $params = [
            'route'            => 'baz',
            'route_parameters' => $routeParams,
            'handler'          => 'my_handler'
        ];
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('edit');
        $this->getOptions()->offsetGet('handler')->shouldReturn('my_handler');
        $this->getOptions()->offsetGet('route')->shouldReturn('baz');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
    }

    function it_doesnt_allow_overriding_frontend_type()
    {
        $params = ['route' => 'foo', 'frontend_type' => 'bar'];
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('redirect');
    }
}
