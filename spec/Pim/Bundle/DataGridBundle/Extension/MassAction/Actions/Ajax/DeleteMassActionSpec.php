<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class DeleteMassActionSpec extends ObjectBehavior
{
    function it_requires_the_entity_name()
    {
        $options = ActionConfiguration::createNamed('delete', []);

        $this->shouldThrow(
            new \LogicException('There is no option "entity_name" for action "delete".')
        )->duringSetOptions($options);
    }

    function it_defines_default_values()
    {
        $params = array('entity_name' => 'foo');
        $options = ActionConfiguration::createNamed('delete', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('delete');
        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('ajax');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn(array());
        $this->getOptions()->offsetGet('handler')->shouldReturn('mass_delete');
        $this->getOptions()->offsetGet('route')->shouldReturn('pim_datagrid_mass_action');
        $this->getOptions()->offsetGet('confirmation')->shouldReturn(true);
    }

    function it_overwrites_default_values()
    {
        $routeParams = array('foo' => 'bar');
        $params = array(
            'route'            => 'baz',
            'route_parameters' => $routeParams,
            'handler'          => 'my_handler',
            'confirmation'     => false,
            'entity_name'      => 'qux'
        );
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('export');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
        $this->getOptions()->offsetGet('handler')->shouldReturn('my_handler');
        $this->getOptions()->offsetGet('route')->shouldReturn('baz');
        $this->getOptions()->offsetGet('confirmation')->shouldReturn(false);
    }

    function it_doesnt_allow_overriding_frontend_type()
    {
        $params = array('entity_name' => 'foo', 'frontend_type' => 'bar');
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('ajax');
    }
}
