<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Widget;

use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Widget\LastOperationsFetcher;
use PhpSpec\ObjectBehavior;

class LastOperationsWidgetSpec extends ObjectBehavior
{
    function let(LastOperationsFetcher $fetcher)
    {
        $this->beConstructedWith($fetcher);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement(WidgetInterface::class);
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('last_operations');
    }

    function it_exposes_the_last_operations_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimImportExportBundle:Widget:last_operations.html.twig');
    }

    function it_has_no_template_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }

    function it_exposes_the_last_operations_data($fetcher)
    {
        $operation = [
            'date' => '01/12/2019',
            'type' => 'import',
            'label' => 'My import',
            'status' => 1,
            'id' => 3,
            'statusLabel' => 'Completed',
            'canSeeReport' => false,
        ];
        $fetcher->fetch()->willReturn([$operation]);

        $this->getData()->shouldReturn([$operation]);
    }
}
