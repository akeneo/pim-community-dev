<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;

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
        $localeResolver
    ) {
        $date = new \DateTime('2012-04-25');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn('01/20/2012');
        $datePresenter->present('2012-04-25', ['locale' => 'en_US'])->willReturn('04/25/2012');

        $this->present($date, ['data' => '2012-04-25'])->shouldReturn([
            'before' => '01/20/2012',
            'after' => '04/25/2012'
        ]);
    }

    function it_presents_only_new_date_when_no_previous_date_is_set(
        $datePresenter,
        $localeResolver
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present(null, ['locale' => 'en_US'])->willReturn('');
        $datePresenter->present('2012-04-25', ['locale' => 'en_US'])->willReturn('04/25/2012');

        $this->present(null, ['data' => '2012-04-25'])->shouldReturn([
            'before' => '',
            'after' => '04/25/2012'
        ]);
    }

    function it_presents_only_old_date_when_no_new_date_is_set(
        $datePresenter,
        $localeResolver
    ) {
        $date = new \DateTime('2012-01-20');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn('2012/20/01');
        $datePresenter->present(null, ['locale' => 'en_US'])->willReturn('');

        $this->present($date, ['data' => ''])->shouldReturn([
            'before' => '2012/20/01',
            'after' => '',
        ]);
    }
}
