<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

class DatePresenterSpec extends ObjectBehavior
{
    function let(
        BasePresenterInterface $datePresenter,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($datePresenter, $localeResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_change_if_it_has_a_date_key()
    {
        $this->supports('pim_catalog_date')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_presents_date_change_using_the_injected_renderer(
        $datePresenter,
        $localeResolver,
        RendererInterface $renderer
    ) {
        $date = new \DateTime('2012-04-25');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn('01/20/2012');
        $datePresenter->present('2012-04-25', ['locale' => 'en_US'])->willReturn('04/25/2012');
        $renderer->renderDiff('01/20/2012', '04/25/2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->present($date, ['data' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_new_date_when_no_previous_date_is_set(
        $datePresenter,
        $localeResolver,
        RendererInterface $renderer
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present(null, ['locale' => 'en_US'])->willReturn('');
        $datePresenter->present('2012-04-25', ['locale' => 'en_US'])->willReturn('04/25/2012');

        $renderer->renderDiff('', '04/25/2012')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->present(null, ['data' => '2012-04-25'])->shouldReturn('diff between two dates');
    }

    function it_presents_only_old_date_when_no_new_date_is_set(
        $datePresenter,
        $localeResolver,
        RendererInterface $renderer
    ) {
        $date = new \DateTime('2012-01-20');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn('2012/20/01');
        $datePresenter->present(null, ['locale' => 'en_US'])->willReturn('');
        $renderer->renderDiff('2012/20/01', '')->willReturn('diff between two dates');

        $this->setRenderer($renderer);
        $this->present($date, ['data' => ''])->shouldReturn('diff between two dates');
    }
}
