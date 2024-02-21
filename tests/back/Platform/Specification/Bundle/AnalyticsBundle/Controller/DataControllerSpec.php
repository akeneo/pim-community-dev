<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\Controller;

use Akeneo\Platform\Bundle\AnalyticsBundle\Controller\DataController;
use Akeneo\Tool\Component\Analytics\ChainedDataCollector;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class DataControllerSpec extends ObjectBehavior
{
    function let(ChainedDataCollector $dataCollector)
    {
        $this->beConstructedWith($dataCollector);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DataController::class);
    }

    function it_collects_data($dataCollector)
    {
        $dataCollector->collect('update_checker')->shouldBeCalled();

        $this->collectAction()->shouldReturnAnInstanceOf(Response::class);
    }
}
