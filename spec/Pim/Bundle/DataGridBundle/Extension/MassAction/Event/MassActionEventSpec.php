<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Event;

use PhpSpec\ObjectBehavior;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

class MassActionEventSpec extends ObjectBehavior
{
    function let(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $this->beConstructedWith($datagrid, $massAction, array('foo'));
    }

    function it_is_an_event()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\Event');
    }

    function it_returns_datagrid($datagrid)
    {
        $this->getDatagrid()->shouldReturn($datagrid);
    }

    function it_returns_mass_action($massAction)
    {
        $this->getMassAction()->shouldReturn($massAction);
    }

    function it_returns_objects($objects)
    {
        $this->getObjects()->shouldReturn(array('foo'));
    }
}
