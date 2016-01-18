<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Controller;

use Akeneo\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DataControllerSpec extends ObjectBehavior
{
    function let(DataCollectorInterface $dataCollector)
    {
        $this->beConstructedWith($dataCollector);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\Controller\DataController');
    }

    function it_collects_data($dataCollector)
    {
        $dataCollector->collect()->shouldBeCalled();

        $this->collectAction()->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}
