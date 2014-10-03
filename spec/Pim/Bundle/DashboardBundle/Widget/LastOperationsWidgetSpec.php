<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class LastOperationsWidgetSpec extends ObjectBehavior
{
    function let(JobExecutionManager $manager, TranslatorInterface $translator)
    {
        $this->beConstructedWith($manager, $translator);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('last_operations');
    }

    function it_exposes_the_last_operations_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:last_operations.html.twig');
    }

    function it_has_no_template_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }

    function it_exposes_the_last_operations_data($manager, $translator)
    {
        $date = new \DateTime();
        $operation = [
            'date'   => $date,
            'type'   => 'import',
            'label'  => 'My import',
            'status' => 1,
            'id'     => 3
        ];

        $manager->getLastOperationsData(Argument::type('array'))->willReturn([$operation]);

        $translator->trans('pim_import_export.batch_status.' . $operation['status'])->willReturn('Completed');

        $operation['statusLabel'] = 'Completed';
        $operation['date'] = $date->format('U');
        $this->getData()->shouldReturn([$operation]);
    }
}
