<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Controller;

use Akeneo\Tool\Component\Analytics\ChainedDataCollector;
use PhpSpec\ObjectBehavior;

class DataControllerSpec extends ObjectBehavior
{
    function let(ChainedDataCollector $dataCollector)
    {
        $this->beConstructedWith($dataCollector);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\Controller\DataController');
    }

    function it_collects_data($dataCollector)
    {
        $dataCollector->collect('update_checker')->shouldBeCalled();

        $this->collectAction()->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
