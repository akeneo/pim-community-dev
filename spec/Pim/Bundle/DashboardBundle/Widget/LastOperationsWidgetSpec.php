<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Entity\Repository\WidgetRepository;

class LastOperationsWidgetSpec extends ObjectBehavior
{
    function let(WidgetRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_last_operations_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:last_operations.html.twig');
    }

    function it_exposes_the_last_operations_template_parameters($repository)
    {
        $operation = [
            'date'   => new \DateTime(),
            'type'   => 'import',
            'label'  => 'My import',
            'status' => 'pim_import_export.batch_status.1',
            'id'     => 3
        ];

        $repository->getLastOperationsData()->willReturn([$operation]);

        $this->getParameters()->shouldReturn(['params' => [$operation]]);
    }
}
