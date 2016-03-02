<?php

namespace spec\Pim\Component\Localization\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Factory\NumberFactory;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(NumberFactory $numberFactory, TranslatorInterface $translator)
    {
        $this->beConstructedWith($numberFactory, ['pim_catalog_metric'], $translator);
    }

    function it_supports_metric()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_presents_english_metric(
        $numberFactory,
        $translator,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create([])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12,000.34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(null);
        $translator->trans('KILOGRAM', Argument::any(), Argument::any())->willReturn('Kilogram');
        $this
            ->present(['data' => 12000.34, 'unit' => 'KILOGRAM'])
            ->shouldReturn('12,000.34 Kilogram');
    }

    function it_presents_french_metric(
        $numberFactory,
        $translator,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create(['locale' => 'fr_FR'])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12 000,34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(null);
        $translator->trans('KILOGRAM', Argument::any(), Argument::any())->willReturn('Kilogram');
        $this
            ->present(['data' => 12000.34, 'unit' => 'KILOGRAM'], ['locale' => 'fr_FR'])
            ->shouldReturn('12 000,34 Kilogram');
    }

    function it_disables_grouping_separator(
        $numberFactory,
        $translator,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create(['disable_grouping_separator' => true])->willReturn($numberFormatter);
        $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '')->willReturn(null);
        $numberFormatter->format(12000.34)->willReturn('12000.34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(null);
        $translator->trans('KILOGRAM', Argument::any(), Argument::any())->willReturn('Kilogram');
        $this
            ->present(['data' => 12000.34, 'unit' => 'KILOGRAM'], ['disable_grouping_separator' => true])
            ->shouldReturn('12000.34 Kilogram');
    }
}
