<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use PhpSpec\ObjectBehavior;

class MassActionEventSpec extends ObjectBehavior
{
    function let(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $this->beConstructedWith($datagrid, $massAction, ['foo']);
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
