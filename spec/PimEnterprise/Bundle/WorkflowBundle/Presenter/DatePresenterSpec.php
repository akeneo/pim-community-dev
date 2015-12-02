<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class DatePresenterSpec extends ObjectBehavior
{
    function let(PresenterInterface $datePresenter, LocaleResolver $localeResolver)
    {
        $this->beConstructedWith($datePresenter, $localeResolver);
    }

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
        $datePresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value,
        \DateTime $date
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn('01/20/2012');
        $datePresenter->present('2012-04-25', ['locale' => 'en_US'])->willReturn('04/25/2012');
        $value->getData()->willReturn($date);

        $renderer->renderOriginalDiff('01/20/2012', '04/25/2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_new_date_when_no_previous_date_is_set(
        $datePresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present(null, ['locale' => 'en_US'])->willReturn('');
        $datePresenter->present('2012-04-25', ['locale' => 'en_US'])->willReturn('04/25/2012');
        $value->getData()->willReturn(null);

        $renderer->renderOriginalDiff('', '04/25/2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_old_date_when_no_new_date_is_set(
        $datePresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value,
        \DateTime $date
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn('2012/20/01');
        $datePresenter->present(null, ['locale' => 'en_US'])->willReturn('');
        $value->getData()->willReturn($date);

        $renderer->renderOriginalDiff('2012/20/01', '')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => ''])->shouldReturn('diff between two dates');
    }
}
