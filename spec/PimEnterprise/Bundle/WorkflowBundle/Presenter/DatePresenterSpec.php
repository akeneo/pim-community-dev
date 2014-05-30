<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

class DatePresenterSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_date_key(
        Model\AbstractProductValue $value
    ) {
        $this->supports($value, ['date' => '2012-04-25'])->shouldBe(true);
    }

    function it_presents_date_change_using_the_injected_renderer(
        $renderer,
        $factory,
        \Diff $diff,
        Model\AbstractProductValue $value,
        \DateTime $date
    ) {
        $value->getData()->willReturn($date);
        $date->format('F, d Y')->willReturn('January, 20 2012');

        $factory->create('January, 20 2012', 'April, 25 2012')->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between two dates');

        $this->present($value, ['date' => '2012-04-25'])->shouldReturn('diff between two dates');
    }
}
