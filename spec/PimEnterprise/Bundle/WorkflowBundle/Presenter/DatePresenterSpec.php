<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class DatePresenterSpec extends ObjectBehavior
{
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
        RendererInterface $renderer,
        Model\AbstractProductValue $value,
        \DateTime $date
    ) {
        $value->getData()->willReturn($date);
        $date->format('F, d Y')->willReturn('January, 20 2012');

        $renderer->renderDiff('January, 20 2012', 'April, 25 2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->present($value, ['date' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_new_date_when_no_previous_date_is_set(
        RendererInterface $renderer,
        Model\AbstractProductValue $value
    ) {
        $value->getData()->willReturn(null);

        $renderer->renderDiff('', 'April, 25 2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->present($value, ['date' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_old_date_when_no_new_date_is_set(
        RendererInterface $renderer,
        Model\AbstractProductValue $value,
        \DateTime $date
    ) {
        $value->getData()->willReturn($date);
        $date->format('F, d Y')->willReturn('January, 20 2012');

        $renderer->renderDiff('January, 20 2012', '')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->present($value, ['date' => ''])->shouldReturn('diff between two dates');
    }
}
