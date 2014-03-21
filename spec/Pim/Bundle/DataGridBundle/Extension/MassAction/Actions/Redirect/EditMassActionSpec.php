<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class EditMassActionSpec extends ObjectBehavior
{
    function it_should_have_required_route()
    {
        $params = array();
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->shouldThrow(
            new \LogicException('There is no option "route" for action "edit".')
        );
    }

    function it_should_define_default_values()
    {
        $params = array('route' => 'foo');
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('edit');
        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('redirect');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn(array());
        $this->getOptions()->offsetGet('handler')->shouldReturn('mass_edit');
    }

    function it_should_overwrite_default_values()
    {
        $routeParams = array('foo' => 'bar');
        $params = array(
            'route'            => 'baz',
            'route_parameters' => $routeParams,
            'handler'          => 'my_handler'
        );
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('edit');
        $this->getOptions()->offsetGet('handler')->shouldReturn('my_handler');
        $this->getOptions()->offsetGet('route')->shouldReturn('baz');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
    }

    function it_should_be_impossible_to_override_frontend()
    {
        $params = array('route' => 'foo', 'frontend_type' => 'bar');
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('redirect');
    }
}
