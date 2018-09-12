<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Prophecy\Argument;

class DeleteMassActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteMassAction::class);
        $this->shouldImplement(MassActionInterface::class);
    }

    function it_overwrites_default_values()
    {
        $routeParams = ['foo' => 'bar'];
        $params = [
            'route'            => 'baz',
            'route_parameters' => $routeParams,
            'handler'          => 'my_handler',
            'confirmation'     => false,
            'entity_name'      => 'qux'
        ];
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('export');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
        $this->getOptions()->offsetGet('handler')->shouldReturn('my_handler');
        $this->getOptions()->offsetGet('route')->shouldReturn('baz');
        $this->getOptions()->offsetGet('confirmation')->shouldReturn(false);
    }
}
