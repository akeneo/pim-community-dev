<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class DatePresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_date_key()
    {
        $this->supportsChange('pim_catalog_date')->shouldBe(true);
        $this->supportsChange('other')->shouldBe(false);
    }

    function it_presents_date_change_using_the_injected_renderer(
        RendererInterface $renderer,
        ProductValueInterface $value,
        \DateTime $date
    ) {
        $value->getData()->willReturn($date);
        $date->format('F, d Y')->willReturn('January, 20 2012');

        $renderer->renderOriginalDiff('January, 20 2012', 'April, 25 2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_new_date_when_no_previous_date_is_set(
        RendererInterface $renderer,
        ProductValueInterface $value
    ) {
        $value->getData()->willReturn(null);

        $renderer->renderOriginalDiff('', 'April, 25 2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_old_date_when_no_new_date_is_set(
        RendererInterface $renderer,
        ProductValueInterface $value,
        \DateTime $date
    ) {
        $value->getData()->willReturn($date);
        $date->format('F, d Y')->willReturn('January, 20 2012');

        $renderer->renderOriginalDiff('January, 20 2012', '')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => ''])->shouldReturn('diff between two dates');
    }
}
