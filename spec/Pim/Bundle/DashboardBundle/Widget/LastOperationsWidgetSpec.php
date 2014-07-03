<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager;

class LastOperationsWidgetSpec extends ObjectBehavior
{
    function let(JobExecutionManager $manager)
    {
        $this->beConstructedWith($manager);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_last_operations_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:last_operations.html.twig');
    }

    function it_exposes_the_last_operations_template_parameters($manager)
    {
        $operation = [
            'date'   => new \DateTime(),
            'type'   => 'import',
            'label'  => 'My import',
            'status' => 'pim_import_export.batch_status.1',
            'id'     => 3
        ];

        $manager->getLastOperationsData(Argument::type('array'))->willReturn([$operation]);

        $this->getParameters()->shouldReturn(['params' => [$operation]]);
    }
}
