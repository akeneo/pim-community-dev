<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Event;

use PhpSpec\ObjectBehavior;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

class MassActionEventSpec extends ObjectBehavior
{
    function let(DatagridInterface $datagrid, MassActionInterface $massAction) {
        $this->beConstructedWith($datagrid, $massAction, array('foo'));
    }

    function it_should_be_an_event() {
        $this->beAnInstanceOf('Symfony\Component\EventDispatcher\Event');
    }

    function it_should_return_datagrid($datagrid) {
        $this->getDatagrid()->shouldReturn($datagrid);
    }

    function it_should_return_mass_action($massAction) {
        $this->getMassAction()->shouldReturn($massAction);
    }

    function it_should_return_objects($objects) {
        $this->getObjects()->shouldReturn(array('foo'));
    }
}
