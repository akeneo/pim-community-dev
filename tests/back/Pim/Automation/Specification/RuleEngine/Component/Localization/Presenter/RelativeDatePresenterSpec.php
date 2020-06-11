<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Localization\Presenter;

use Akeneo\Pim\Automation\RuleEngine\Component\Localization\Presenter\RelativeDatePresenter;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class RelativeDatePresenterSpec extends ObjectBehavior
{
    function let(PresenterInterface $basePresenter, TranslatorInterface $translator)
    {
        $this->beConstructedWith($basePresenter, $translator);
    }

    function it_is_a_presenter()
    {
        $this->shouldImplement(PresenterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RelativeDatePresenter::class);
    }

    function it_presents_dates(PresenterInterface $basePresenter)
    {
        $basePresenter->supports('updated')->willReturn(true);
        $basePresenter->supports(Argument::any())->willReturn(false);

        $this->supports('updated')->shouldReturn(true);
        $this->supports('categories')->shouldReturn(false);
    }

    function it_does_not_present_a_relative_date_without_the_option(
        PresenterInterface $basePresenter,
        TranslatorInterface $translator
    ) {
        $options = [
            'locale' => 'en_US',
            'timezone' => 'Europe/Paris',
        ];

        $basePresenter->present('-2 days', $options)->shouldBeCalled()->willReturn('06/08/2020 8:00 AM');
        $translator->trans(Argument::cetera())->shouldNotBeCalled();

        $this->present('-2 days', $options)->shouldReturn('06/08/2020 8:00 AM');
    }

    function it_does_not_present_if_the_value_is_not_a_relative_date(
        PresenterInterface $basePresenter,
        TranslatorInterface $translator
    ) {
        $options = [
            'locale' => 'en_US',
            'timezone' => 'Europe/Paris',
            'present_relative_date' => true,
        ];

        $basePresenter->present('2020-06-08 06:00:00', $options)->shouldBeCalled()->willReturn('06/08/2020 8:00 AM');
        $translator->trans(Argument::cetera())->shouldNotBeCalled();

        $this->present('2020-06-08 06:00:00', $options)->shouldReturn('06/08/2020 8:00 AM');
    }

    function it_presents_a_relative_date(
        PresenterInterface $basePresenter,
        TranslatorInterface $translator
    ) {
        $options = [
            'locale' => 'en_US',
            'timezone' => 'Europe/Paris',
            'present_relative_date' => true,
        ];

        $basePresenter->present('-2 days', $options)->shouldBeCalled()->willReturn('06/08/2020 8:00 AM');
        $translator->trans(
            'pimee_catalog_rule.datetime.relative_date.day',
            [
                '%count%' => -2,
                '%absolute_count%' => 2,
            ],
            'messages',
            'en_US'
        )->shouldBeCalled()->willReturn('2 days ago');

        $this->present('-2 days', $options)->shouldReturn('06/08/2020 8:00 AM (2 days ago)');
    }
}
