<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class DeleteMassActionSpec extends ObjectBehavior
{
    function it_should_have_required_entity_name()
    {
        $params = array();
        $options = ActionConfiguration::createNamed('delete', $params);

        $this->shouldThrow(
            new \LogicException('There is no option "entity_name" for action "delete".')
        );
    }
}
