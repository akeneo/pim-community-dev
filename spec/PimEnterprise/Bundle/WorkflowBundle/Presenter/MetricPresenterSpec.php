<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\Metric;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_metric_key()
    {
        $this->supportsChange(['metric' => 'foo'])->shouldBe(true);
    }

    function it_presents_metric_change_using_the_injected_renderer(
        $renderer,
        $factory,
        \Diff $diff,
        Metric $metric
    ) {
        $metric->getData()->willReturn(50);
        $metric->getUnit()->willReturn('kilogram');

        $factory->create('50 kilogram', '123 millimeter')->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between two metrics');

        $this->present($metric, ['metric' => ['unit' => 'millimeter', 'data' => '123']])->shouldReturn('diff between two metrics');
    }
}
