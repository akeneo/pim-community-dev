<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\DashboardBundle\Entity\Repository\WidgetRepository;

class LastOperationsWidgetSpec extends ObjectBehavior
{
    function let(SecurityFacade $securityFacade, WidgetRepository $repository)
    {
        $securityFacade->isGranted(Argument::any())->willReturn(true);
        $this->beConstructedWith($securityFacade, $repository);
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

        $repository->getLastOperationsData(Argument::type('array'))->willReturn([$operation]);

        $this->getParameters()->shouldReturn(['params' => [$operation]]);
    }
}
